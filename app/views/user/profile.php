<?php $title = "My Profile"; // $data is passed from controller, e.g., $user ?>

<div class="profile-container">
    <h2><?php echo htmlspecialchars($first_name ?? 'User'); ?>'s Profile</h2>

    <?php if (isset($errors['update_err'])): ?>
        <p class="message error"><?php echo htmlspecialchars($errors['update_err']); ?></p>
    <?php endif; ?>
    
    <form action="<?php echo APP_URL; ?>/user/profile" method="POST" enctype="multipart/form-data" id="profileForm">
        <div class="profile-grid">
            <div class="profile-picture-section">
                <h3>Profile Picture</h3>
                <img src="<?php echo isset($profile_picture_path) && $profile_picture_path ? APP_URL . '/' . htmlspecialchars($profile_picture_path) : APP_URL . '/img/default-avatar.png'; ?>" alt="Profile Picture" id="profileImagePreview" class="profile-img-large">
                <input type="file" name="profile_picture" id="profile_picture" accept="image/*" onchange="previewImage(event)">
                <?php if (isset($errors['profile_picture_err'])): ?><span class="error-text"><?php echo htmlspecialchars($errors['profile_picture_err']); ?></span><?php endif; ?>
            </div>

            <div class="profile-details-section">
                <h3>Personal Information</h3>
                <div class="form-group">
                    <label for="first_name">First Name:</label>
                    <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($first_name ?? ''); ?>" required>
                    <?php if (isset($errors['first_name_err'])): ?><span class="error-text"><?php echo htmlspecialchars($errors['first_name_err']); ?></span><?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name:</label>
                    <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($last_name ?? ''); ?>" required>
                    <?php if (isset($errors['last_name_err'])): ?><span class="error-text"><?php echo htmlspecialchars($errors['last_name_err']); ?></span><?php endif; ?>
                </div>
                 <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email_display" value="<?php echo htmlspecialchars($email ?? ''); ?>" disabled>
                    <small>Email cannot be changed here.</small>
                </div>
                <div class="form-group">
                    <label for="birth_date">Date of Birth:</label>
                    <input type="date" id="birth_date" name="birth_date_display" value="<?php echo htmlspecialchars($birth_date ?? ''); ?>" disabled>
                     <small>Birth date cannot be changed here.</small>
                </div>

                <h3>Profile Details</h3>
                 <div class="form-group">
                    <label for="description">Short Description:</label>
                    <textarea id="description" name="description" rows="3"><?php echo htmlspecialchars($description ?? ''); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="preferences">Love Preferences:</label>
                    <input type="text" id="preferences" name="preferences" value="<?php echo htmlspecialchars($preferences ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="relation_type">Type of Relation Sought:</label>
                    <input type="text" id="relation_type" name="relation_type" value="<?php echo htmlspecialchars($relation_type ?? ''); ?>" placeholder="e.g., Friendship, Serious Relationship">
                </div>
                <div class="form-group">
                    <label for="interests">Interests (comma separated):</label>
                    <input type="text" id="interests" name="interests" value="<?php echo htmlspecialchars($interests ?? ''); ?>" placeholder="e.g., hiking, movies, coding">
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Update Profile</button>
    </form>
</div>
<script src="<?php echo APP_URL; ?>/js/profile.js"></script>
