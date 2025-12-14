<?php
session_start();
require_once "../backend/db.php";

$account_id = $_SESSION["account_id"];

$stmt = $conn->prepare("
    SELECT *
    FROM notifications
    WHERE account_id = ?
    ORDER BY created_at DESC
");
$stmt->execute([$account_id]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Mark all as read
$conn->prepare("UPDATE notifications SET is_read = 1 WHERE account_id = ?")
    ->execute([$account_id]);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Notifications | GameX</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/organizer_modern.css">
    <link rel="stylesheet" href="../assets/css/gaming_modern.css">
    <link rel="stylesheet" href="../assets/css/player_sidebar_gaming.css">
</head>

<body style="background: var(--bg-primary); min-height: 100vh;">
    <?php include "../includes/player/player_sidebar.php"; ?>

    <main class="org-main">
        <div class="gaming-hero" style="min-height: 250px;">
            <div class="gaming-hero__orb gaming-hero__orb--primary" style="background: radial-gradient(circle, rgba(59,130,246,0.15), transparent 70%);"></div>
            <div class="gaming-hero__orb gaming-hero__orb--secondary"></div>

            <div class="gaming-hero__content">
                <div class="gaming-hero__badge">
                    <div class="gaming-hero__badge-pulse" style="background: #3b82f6;"></div>
                    <i class="fas fa-bell" style="color: #3b82f6; font-size: 1.1rem;"></i>
                    <span style="color: var(--text-primary); font-weight: 700; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px;">Notifications</span>
                </div>
            </div>

            <h1 style="font-size: 3rem; font-weight: 900; margin: 0 0 0.75rem 0; letter-spacing: -1.5px; color: #fafafa;">
                My Notifications
            </h1>

            <p style="font-size: 1.1rem; color: #a1a1aa; max-width: 600px;">Stay updated with all your important notifications and announcements</p>
        </div>
        </section>

        <style>
            @keyframes gridMove {
                0% {
                    background-position: 0 0;
                }

                100% {
                    background-position: 50px 50px;
                }
            }

            @keyframes float {

                0%,
                100% {
                    transform: translate(0, 0);
                }

                50% {
                    transform: translate(20px, -20px);
                }
            }

            @keyframes pulse {

                0%,
                100% {
                    opacity: 1;
                    transform: scale(1);
                }

                50% {
                    opacity: 0.6;
                    transform: scale(1.3);
                }
            }
        </style>

        <div style="padding: 0 2rem 2rem; max-width: 1200px; margin: 0 auto;">
            <?php if (empty($notifications)): ?>
                <!-- Empty State -->
                <div style="position: relative; background: linear-gradient(135deg, #18181b 0%, #1a1a1d 100%); border: 1px solid rgba(255,255,255,0.05); border-radius: 20px; overflow: hidden; padding: 4rem 2rem; text-align: center;">
                    <div style="position: absolute; inset: 0; background-image: radial-gradient(circle at 20px 20px, rgba(255,255,255,0.02) 1px, transparent 1px); background-size: 20px 20px; opacity: 0.5;"></div>
                    <div style="position: relative; max-width: 400px; margin: 0 auto;">
                        <div style="width: 100px; height: 100px; margin: 0 auto 2rem; background: rgba(59,130,246,0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; position: relative;">
                            <i class="fas fa-bell-slash" style="font-size: 3rem; color: #3b82f6; opacity: 0.7;"></i>
                            <div style="position: absolute; inset: -5px; border-radius: 50%; border: 2px solid rgba(59,130,246,0.2); animation: pulse 2s ease-in-out infinite;"></div>
                        </div>
                        <h3 style="color: #fafafa; font-size: 1.75rem; font-weight: 800; margin: 0 0 1rem 0; letter-spacing: -0.5px;">No Notifications Yet</h3>
                        <p style="color: #71717a; font-size: 1.05rem; margin: 0; line-height: 1.6;">You're all caught up! Check back later for new updates and notifications.</p>
                    </div>
                </div>
            <?php else: ?>
                <div style="display: grid; gap: 1.25rem;">
                    <?php foreach ($notifications as $n): ?>
                        <div style="position: relative; background: linear-gradient(135deg, #18181b 0%, #1a1a1d 100%); border: 1px solid rgba(255,255,255,0.05); border-radius: 16px; overflow: hidden; transition: all 0.3s;" onmouseover="this.style.borderColor='rgba(59,130,246,0.3)'; this.style.transform='translateX(6px)'; this.style.background='rgba(24,24,27,0.95)'" onmouseout="this.style.borderColor='rgba(255,255,255,0.05)'; this.style.transform=''; this.style.background='linear-gradient(135deg, #18181b 0%, #1a1a1d 100%)'">
                            <!-- Glow Effect -->
                            <div style="position: absolute; top: 0; left: 0; width: 100px; height: 100%; background: linear-gradient(90deg, rgba(59,130,246,0.08), transparent);"></div>
                            <div style="position: absolute; inset: 0; background-image: radial-gradient(circle at 20px 20px, rgba(255,255,255,0.02) 1px, transparent 1px); background-size: 20px 20px; opacity: 0.5;"></div>

                            <!-- Content -->
                            <div style="position: relative; padding: 1.75rem; display: flex; gap: 1.25rem;">
                                <!-- Icon -->
                                <div style="width: 56px; height: 56px; border-radius: 14px; background: linear-gradient(135deg, #3b82f6, #2563eb); display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 8px 24px rgba(59,130,246,0.4); position: relative;">
                                    <i class="fas fa-bell" style="color: white; font-size: 1.3rem;"></i>
                                    <div style="position: absolute; inset: -3px; border-radius: 14px; border: 2px solid rgba(59,130,246,0.3); animation: pulse 2s ease-in-out infinite;"></div>
                                </div>

                                <!-- Notification Content -->
                                <div style="flex: 1; min-width: 0;">
                                    <h3 style="color: #fafafa; font-size: 1.2rem; font-weight: 700; margin: 0 0 0.5rem 0; letter-spacing: -0.3px;"><?= htmlspecialchars($n['title']) ?></h3>
                                    <p style="color: #a1a1aa; font-size: 0.95rem; margin: 0 0 0.75rem 0; line-height: 1.6;"><?= htmlspecialchars($n['message']) ?></p>
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <div style="width: 20px; height: 20px; background: rgba(16,185,129,0.15); border-radius: 6px; display: flex; align-items: center; justify-content: center;">
                                            <i class="fas fa-clock" style="color: #10b981; font-size: 0.7rem;"></i>
                                        </div>
                                        <small style="color: #71717a; font-size: 0.85rem; font-weight: 500;"><?= $n['created_at'] ?></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

</body>

</html>