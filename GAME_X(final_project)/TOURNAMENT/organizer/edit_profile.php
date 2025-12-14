<?php
session_start();
require_once __DIR__ . '/../backend/db.php';
require_once __DIR__ . '/../backend/helpers/auth_guard.php';

checkAuth('organizer');

$success = "";
$error = "";

// Get organizer profile - fetch accounts and organizer_profiles separately
// First, get account info
$accountStmt = $conn->prepare("SELECT account_id, username, email FROM accounts WHERE account_id = ?");
$accountStmt->execute([$_SESSION['account_id']]);
$accountData = $accountStmt->fetch(PDO::FETCH_ASSOC);

// Then get organizer profile info
$orgStmt = $conn->prepare("SELECT organizer_id, organization, contact_no, website FROM organizer_profiles WHERE account_id = ?");
$orgStmt->execute([$_SESSION['account_id']]);
$orgData = $orgStmt->fetch(PDO::FETCH_ASSOC);

// Manually build profile array to ensure all keys are present
$profile = [
    'account_id' => $accountData['account_id'] ?? $_SESSION['account_id'],
    'username' => $accountData['username'] ?? null,
    'email' => $accountData['email'] ?? null,
    'organizer_id' => $orgData['organizer_id'] ?? null,
    'organization' => $orgData['organization'] ?? null,
    'contact_no' => $orgData['contact_no'] ?? null,
    'website' => $orgData['website'] ?? null
];

if (!$accountData) {
    die("Unable to load account information. Please contact support.");
}

// Sync session data to database if username/email are NULL (data migration)
if ((is_null($profile['username']) || $profile['username'] === '') && !empty($_SESSION['username'])) {
    try {
        $syncStmt = $conn->prepare("UPDATE accounts SET username = ? WHERE account_id = ?");
        $syncStmt->execute([$_SESSION['username'], $_SESSION['account_id']]);
        $profile['username'] = $_SESSION['username'];
    } catch (PDOException $e) {
        // Silent fail - will be handled by validation later
    }
}
if ((is_null($profile['email']) || $profile['email'] === '') && !empty($_SESSION['email'])) {
    try {
        $syncStmt = $conn->prepare("UPDATE accounts SET email = ? WHERE account_id = ?");
        $syncStmt->execute([$_SESSION['email'], $_SESSION['account_id']]);
        $profile['email'] = $_SESSION['email'];
    } catch (PDOException $e) {
        // Silent fail - will be handled by validation later
    }
}

// If organizer profile doesn't exist, create it
if (empty($profile['organizer_id'])) {
    $createStmt = $conn->prepare("
        INSERT INTO organizer_profiles (account_id, organization, contact_no) 
        VALUES (?, ?, ?)
    ");
    $createStmt->execute([
        $_SESSION['account_id'],
        $_SESSION['username'] . "'s Organization",
        '09123456789'
    ]);

    // Fetch again to get the complete profile
    $accountStmt->execute([$_SESSION['account_id']]);
    $accountData = $accountStmt->fetch(PDO::FETCH_ASSOC);
    $orgStmt->execute([$_SESSION['account_id']]);
    $orgData = $orgStmt->fetch(PDO::FETCH_ASSOC);
    $profile = [
        'account_id' => $accountData['account_id'] ?? $_SESSION['account_id'],
        'username' => $accountData['username'] ?? null,
        'email' => $accountData['email'] ?? null,
        'organizer_id' => $orgData['organizer_id'] ?? null,
        'organization' => $orgData['organization'] ?? null,
        'contact_no' => $orgData['contact_no'] ?? null,
        'website' => $orgData['website'] ?? null
    ];
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Get form inputs, use current values if empty
    $username = !empty(trim($_POST['username'] ?? '')) ? trim($_POST['username']) : ($profile['username'] ?? '');
    $email = !empty(trim($_POST['email'] ?? '')) ? trim($_POST['email']) : ($profile['email'] ?? '');
    $organization = !empty(trim($_POST['organization'] ?? '')) ? trim($_POST['organization']) : ($profile['organization'] ?? '');
    $contact_no = !empty(trim($_POST['contact_no'] ?? '')) ? trim($_POST['contact_no']) : ($profile['contact_no'] ?? '');
    $website = !empty(trim($_POST['website'] ?? '')) ? trim($_POST['website']) : ($profile['website'] ?? '');

    // Debug - show what was received vs what's in DB
    $_SESSION['debug_post'] = [
        'username_new' => $username,
        'username_old' => $profile['username'] ?? '',
        'email_new' => $email,
        'email_old' => $profile['email'] ?? '',
        'organization_new' => $organization,
        'organization_old' => $profile['organization'] ?? '',
        'contact_new' => $contact_no,
        'contact_old' => $profile['contact_no'] ?? '',
        'website_new' => $website,
        'website_old' => $profile['website'] ?? ''
    ];

    // Validation - only validate if field is being changed
    $validationError = false;

    if (!empty(trim($_POST['email'] ?? '')) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
        $validationError = true;
    }

    if (!$validationError && !empty(trim($_POST['contact_no'] ?? '')) && !preg_match('/^[0-9+\-() ]+$/', $contact_no)) {
        $error = "Please enter a valid contact number.";
        $validationError = true;
    }

    if (!$validationError) {
        try {
            // Check if email/username is taken by another account (only if changed)
            $checkStmt = $conn->prepare("
                SELECT account_id 
                FROM accounts 
                WHERE (email = ? OR username = ?) AND account_id != ?
            ");
            $checkStmt->execute([$email, $username, $_SESSION['account_id']]);

            if ($checkStmt->fetch()) {
                $error = "Email or username is already taken by another account.";
            } else {
                // Build dynamic update query for accounts (only update if changed)
                $accountUpdates = [];
                $accountParams = [];

                if ($username !== ($profile['username'] ?? '')) {
                    $accountUpdates[] = "username = ?";
                    $accountParams[] = $username;
                }

                if ($email !== ($profile['email'] ?? '')) {
                    $accountUpdates[] = "email = ?";
                    $accountParams[] = $email;
                }

                // Build dynamic update query for organizer_profiles (only update if changed)
                $profileUpdates = [];
                $profileParams = [];

                if ($organization !== ($profile['organization'] ?? '')) {
                    $profileUpdates[] = "organization = ?";
                    $profileParams[] = $organization;
                }

                if ($contact_no !== ($profile['contact_no'] ?? '')) {
                    $profileUpdates[] = "contact_no = ?";
                    $profileParams[] = $contact_no;
                }

                if ($website !== ($profile['website'] ?? '')) {
                    $profileUpdates[] = "website = ?";
                    $profileParams[] = $website ?: null;
                }

                // Only proceed if there were actual changes
                if (!empty($accountUpdates) || !empty($profileUpdates)) {
                    // Begin transaction
                    $conn->beginTransaction();

                    // Execute account updates
                    if (!empty($accountUpdates)) {
                        $accountParams[] = $_SESSION['account_id'];
                        $sql = "UPDATE accounts SET " . implode(', ', $accountUpdates) . " WHERE account_id = ?";
                        $updateAccount = $conn->prepare($sql);
                        $result = $updateAccount->execute($accountParams);

                        if (!$result) {
                            throw new Exception("Failed to update accounts table");
                        }
                    }

                    // Execute profile updates
                    if (!empty($profileUpdates)) {
                        $profileParams[] = $_SESSION['account_id'];
                        $updateProfile = $conn->prepare("
                            UPDATE organizer_profiles 
                            SET " . implode(', ', $profileUpdates) . "
                            WHERE account_id = ?
                        ");
                        $updateProfile->execute($profileParams);
                    }

                    // Commit transaction
                    $conn->commit();

                    // Update session only if account fields were changed
                    if (!empty($accountUpdates)) {
                        $_SESSION['username'] = $username;
                        $_SESSION['email'] = $email;
                    }

                    // Set success message and redirect to prevent form resubmission
                    $_SESSION['success_message'] = "Profile updated successfully!";
                } else {
                    $_SESSION['success_message'] = "No changes were made.";
                }
                header("Location: edit_profile.php");
                exit;
            }
        } catch (PDOException $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            $error = "Database error: " . htmlspecialchars($e->getMessage());
            $_SESSION['debug_post']['caught_exception'] = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - Game X</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Modern Organizer CSS -->
    <link rel="stylesheet" href="../assets/css/organizer_modern.css">
</head>

<body>
    <?php include '../includes/organizer/organizer_sidebar.php'; ?>

    <main class="org-main">
        <!-- Hero Section -->
        <section class="org-hero">
            <div class="org-hero-content">
                <div class="org-hero-badge">
                    <i class="fas fa-user-edit"></i>
                    Profile Settings
                </div>
                <h1>Edit Your Profile 👤</h1>
                <p>Update your organization information and contact details</p>
            </div>
        </section>

        <!-- Edit Profile Form -->
        <div class="org-card" style="max-width: 800px; margin: 40px auto;">
            <?php if (!empty($_SESSION['success_message'])): ?>
                <div class="org-alert-success" style="margin-bottom: 24px;">
                    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_SESSION['success_message']) ?>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="org-alert-success" style="margin-bottom: 24px;">
                    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="org-alert-error" style="margin-bottom: 24px;">
                    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="org-form">
                <!-- Account Information -->
                <div class="org-form-section">
                    <h3 class="org-form-section-title">
                        <i class="fas fa-user"></i> Account Information
                    </h3>

                    <div class="org-form-row">
                        <div class="org-form-group">
                            <label for="username" class="org-form-label">
                                Username
                            </label>
                            <input
                                type="text"
                                id="username"
                                name="username"
                                class="org-form-control"
                                value=""
                                placeholder="<?= htmlspecialchars($profile['username'] ?? 'Not set') ?>">
                        </div>

                        <div class="org-form-group">
                            <label for="email" class="org-form-label">
                                Email Address
                            </label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                class="org-form-control"
                                value=""
                                placeholder="<?= htmlspecialchars($profile['email'] ?? 'Not set') ?>">
                        </div>
                    </div>
                </div>

                <!-- Organization Information -->
                <div class="org-form-section">
                    <h3 class="org-form-section-title">
                        <i class="fas fa-building"></i> Organization Details
                    </h3>

                    <div class="org-form-group">
                        <label for="organization" class="org-form-label">
                            Organization Name
                        </label>
                        <input
                            type="text"
                            id="organization"
                            name="organization"
                            class="org-form-control"
                            value=""
                            placeholder="<?= htmlspecialchars($profile['organization'] ?? 'Not set') ?>">
                    </div>

                    <div class="org-form-row">
                        <div class="org-form-group">
                            <label for="contact_no" class="org-form-label">
                                Contact Number
                            </label>
                            <input
                                type="text"
                                id="contact_no"
                                name="contact_no"
                                class="org-form-control"
                                value=""
                                placeholder="<?= htmlspecialchars($profile['contact_no'] ?? 'Not set') ?>">
                        </div>

                        <div class="org-form-group">
                            <label for="website" class="org-form-label">
                                Website <span class="optional">(Optional)</span>
                            </label>
                            <input
                                type="text"
                                id="website"
                                name="website"
                                class="org-form-control"
                                value=""
                                placeholder="<?= htmlspecialchars($profile['website'] ?? 'Not set') ?>">
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="org-form-actions" style="margin-top: 32px;">
                    <button type="submit" class="org-btn">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                    <a href="organizer_dashboard.php" class="org-btn org-btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </main>

    <style>
        .org-form-section {
            margin-bottom: 32px;
            padding-bottom: 32px;
            border-bottom: 1px solid var(--border);
        }

        .org-form-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .org-form-section-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .org-form-section-title i {
            color: var(--accent);
        }

        .org-form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .org-form-group {
            margin-bottom: 20px;
        }

        .org-form {
            display: block;
        }

        .required {
            color: var(--accent);
            font-weight: 600;
        }

        .optional {
            color: var(--text-muted);
            font-size: 12px;
            font-weight: 400;
        }

        @media (max-width: 768px) {
            .org-form-row {
                grid-template-columns: 1fr;
            }
        }

        /* Profile Information Display Styles */
        .profile-info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }

        .info-section {
            background: rgba(255, 94, 0, 0.05);
            padding: 20px;
            border-radius: 12px;
            border: 1px solid var(--border);
        }

        .info-section-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .info-section-title i {
            color: var(--accent);
        }

        .info-items {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .info-label {
            font-size: 12px;
            font-weight: 500;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-value {
            font-size: 15px;
            font-weight: 500;
            color: var(--text-main);
            word-break: break-word;
        }

        .info-link {
            color: var(--accent);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: color 0.3s ease;
        }

        .info-link:hover {
            color: var(--accent-hover);
            text-decoration: underline;
        }

        .info-link i {
            font-size: 12px;
        }

        @media (max-width: 768px) {
            .profile-info-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</body>

</html>