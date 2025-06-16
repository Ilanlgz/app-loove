<?php include BASE_PATH . '/app/views/layout/header.php'; ?>
<?php include BASE_PATH . '/app/views/layout/navbar.php'; ?>

<style>
.no-conversations {
    text-align: center;
    padding: 4rem 2rem;
    background: white;
    border-radius: 15px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.btn-discover {
    background: #FF4458;
    color: white;
    padding: 1rem 2rem;
    text-decoration: none;
    border-radius: 25px;
    font-weight: bold;
    display: inline-block;
    margin-top: 1rem;
    transition: all 0.3s ease;
}

.messages-container {
    display: grid;
    grid-template-columns: 1fr 2fr;
    gap: 2rem;
    height: 70vh;
}

.conversations-list {
    background: white;
    border-radius: 15px;
    overflow-y: auto;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.conversation-item {
    display: flex;
    align-items: center;
    padding: 1rem;
    border-bottom: 1px solid #e2e8f0;
    cursor: pointer;
    transition: background 0.3s;
}

.conversation-item:hover {
    background: #f8fafc;
}

.conv-avatar {
    width: 50px;
    height: 50px;
    background: #FF4458;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    margin-right: 1rem;
}

.chat-area {
    background: white;
    border-radius: 15px;
    display: flex;
    flex-direction: column;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.no-chat-selected {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    background: white;
    border-radius: 15px;
    color: #64748b;
    text-align: center;
}
</style>

<div class="container">
    <h1 style='margin-bottom: 2rem;'>Messages</h1>
    
    <div class="no-conversations">
        <h2>Aucune conversation</h2>
        <p>Vous n'avez pas encore de conversations. Allez découvrir des profils pour créer des matchs !</p>
        <a href="/loove/public/discover" class="btn-discover">Découvrir des profils</a>
    </div>
</div>

<?php include BASE_PATH . '/app/views/layout/footer.php'; ?>
