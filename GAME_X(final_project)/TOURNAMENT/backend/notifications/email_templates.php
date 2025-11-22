<?php

// -----------------------------
// 1. WELCOME EMAIL (NEW ACCOUNT)
// -----------------------------
function emailTemplate_Welcome($username) {
    return "
    <div style='font-family: Arial; padding: 20px; background: #f7f7f7;'>
        <div style='max-width: 600px; margin: auto; background: #fff; padding: 20px;'>
            <h2 style='color: #0066cc;'>🎮 Welcome to GameX, $username!</h2>
            <p>We're excited to have you join our gaming community!</p>
            <p>You can now register for tournaments, create teams, and compete with players.</p>
            <hr>
            <p style='font-size: 12px; color: gray;'>This email was sent automatically by GameX.</p>
        </div>
    </div>";
}


// -------------------------------------
// 2. NEW TOURNAMENT ANNOUNCEMENT EMAIL
// -------------------------------------
function emailTemplate_NewTournament($title, $description) {
    return "
    <div style='font-family: Arial; padding: 20px; background: #f1f1f1;'>
        <div style='max-width: 600px; margin: auto; background: #fff; padding: 20px;'>
            <h2 style='color: #d9534f;'>🔥 New Tournament Available!</h2>
            <h3>$title</h3>
            <p>$description</p>
            <a href='http://localhost/GAME_X(final_project)/TOURNAMENT/tournament.php'
               style='background: #d9534f; color: white; padding: 10px 15px; text-decoration: none;'>View Tournament</a>
            <hr>
            <p style='font-size: 12px; color: gray;'>GameX Tournament System</p>
        </div>
    </div>";
}


// ------------------------
// 3. ADMIN REPLY TEMPLATE
// ------------------------
function emailTemplate_AdminReply($replyMessage, $playerName = "Player") {
    return "
    <div style='font-family: Arial; padding: 20px; background: #fafafa;'>
        <div style='max-width: 600px; margin: auto; background: white; padding: 20px;'>
            <h2 style='color: #5bc0de;'>💬 Admin Response</h2>
            <p>Hello <strong>$playerName</strong>,</p>
            <p>The admin has responded to your message:</p>
            
            <div style='border-left: 4px solid #5bc0de; padding-left: 10px; margin: 10px 0;'>
                <p>$replyMessage</p>
            </div>

            <p>If you need further assistance, feel free to message us again.</p>
            <hr>
            <p style='font-size: 12px; color: gray;'>GameX Support Team</p>
        </div>
    </div>";
}


// ----------------------------
// 4. TEAM INVITATION TEMPLATE
// ----------------------------
function emailTemplate_TeamInvite($teamName) {
    return "
    <div style='font-family: Arial; padding: 20px; background: #f8f8f8;'>
        <div style='max-width: 600px; margin: auto; background: #fff; padding: 20px;'>
            <h2 style='color: #0275d8;'>👥 Team Invitation</h2>
            <p>You have been invited to join team:</p>
            <h3 style='color: #5cb85c;'>$teamName</h3>
            <p>Login to your dashboard to accept or decline the invitation.</p>
            <hr>
            <p style='font-size: 12px; color: gray;'>GameX Team Management</p>
        </div>
    </div>";
}


// ---------------------------------------------
// 5. SYSTEM UPDATE / MAINTENANCE ANNOUNCEMENT
// ---------------------------------------------
function emailTemplate_SystemUpdate($title, $content) {
    return "
    <div style='font-family: Arial; padding: 20px; background: #eeeeee;'>
        <div style='max-width: 600px; margin: auto; background: #ffffff; padding: 20px;'>
            <h2 style='color: #f0ad4e;'>📢 System Announcement</h2>
            <h3>$title</h3>
            <p>$content</p>
            <hr>
            <p style='font-size: 12px; color: gray;'>GameX Notifications</p>
        </div>
    </div>";
}
?>
