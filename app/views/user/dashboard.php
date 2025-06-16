<?php $title = $title ?? "My Dashboard"; ?>

<div class="dashboard-container">
    <h2>Welcome to your Dashboard, <?php echo htmlspecialchars($user['first_name'] ?? 'User'); ?>!</h2>
    
    <div class="dashboard-sections">
        <section class="dashboard-section">
            <h3>My Profile</h3>
            <p>Keep your profile up-to-date to attract the best matches.</p>
            <a href="<?php echo APP_URL; ?>/profile" class="btn btn-secondary">Edit Profile</a>
            <?php if (isset($user['profile_picture_path']) && $user['profile_picture_path']): ?>
                <img src="<?php echo APP_URL . '/' . htmlspecialchars($user['profile_picture_path']); ?>" alt="Your profile picture" class="dashboard-profile-pic">
            <?php else: ?>
                 <img src="<?php echo APP_URL . '/img/default-avatar.png'; ?>" alt="Default profile picture" class="dashboard-profile-pic">
            <?php endif; ?>
        </section>

        <section class="dashboard-section">
            <h3>Find Matches</h3>
            <p>Start searching for compatible partners now.</p>
            <a href="<?php echo APP_URL; ?>/search" class="btn btn-primary">Search Profiles</a>
        </section>

        <section class="dashboard-section">
            <h3>My Matches</h3>
            <p>View users you've matched with.</p>
            <!-- Placeholder for matches list -->
            <p><em>Matches feature coming soon.</em></p>
            <a href="<?php echo APP_URL; ?>/matches" class="btn btn-secondary">View Matches</a>
        </section>

        <section class="dashboard-section">
            <h3>Messages</h3>
            <p>Check your latest conversations.</p>
            <!-- Placeholder for messages list -->
            <p><em>Messaging feature coming soon.</em></p>
            <a href="<?php echo APP_URL; ?>/messages" class="btn btn-secondary">View Messages</a>
        </section>

        <?php 
        // Placeholder for premium features
        // $subscriptionModel = new \App\Models\Subscription(); // Or get from controller
        // $activeSubscription = $subscriptionModel->getUserActiveSubscription($_SESSION['user_id']);
        $activeSubscription = false; // Placeholder
        if (!$activeSubscription): 
        ?>
        <section class="dashboard-section premium-upsell">
            <h3>Go Premium!</h3>
            <p>Unlock exclusive features like seeing who viewed your profile, advanced search filters, and more!</p>
            <a href="<?php echo APP_URL; ?>/subscription" class="btn btn-special">Upgrade to Premium</a>
        </section>
        <?php else: ?>
        <section class="dashboard-section">
            <h3>Premium Member</h3>
            <p>You have access to all premium features. Enjoy!</p>
            <p>Your plan: <?php /* echo htmlspecialchars($activeSubscription['plan_name']); */ ?> (Expires: <?php /* echo htmlspecialchars(date('M d, Y', strtotime($activeSubscription['end_date']))); */ ?>)</p>
        </section>
        <?php endif; ?>
    </div>
</div>
