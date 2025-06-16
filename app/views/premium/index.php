<?php include BASE_PATH . '/app/views/layout/header.php'; ?>
<?php include BASE_PATH . '/app/views/layout/navbar.php'; ?>

<style>
.premium-hero {
    background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
    color: #2d3748;
    padding: 3rem;
    border-radius: 20px;
    text-align: center;
    margin-bottom: 3rem;
}

.premium-plans {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
    margin-bottom: 3rem;
}

.plan-card {
    background: white;
    border: 2px solid #e2e8f0;
    border-radius: 15px;
    padding: 2rem;
    text-align: center;
    transition: all 0.3s ease;
}

.plan-card.featured {
    border-color: #FFD700;
    transform: scale(1.05);
    box-shadow: 0 10px 30px rgba(255, 215, 0, 0.3);
}

.plan-price {
    font-size: 2.5rem;
    font-weight: bold;
    color: #FF4458;
    margin: 1rem 0;
}

.plan-features {
    list-style: none;
    margin: 1.5rem 0;
}

.plan-features li {
    padding: 0.5rem 0;
    color: #4a5568;
}

.btn-premium {
    background: linear-gradient(135deg, #FFD700, #FFA500);
    color: #2d3748;
    padding: 1rem 2rem;
    border: none;
    border-radius: 25px;
    font-weight: bold;
    cursor: pointer;
    width: 100%;
}

.payment-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.8);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.payment-form {
    background: white;
    padding: 2rem;
    border-radius: 15px;
    max-width: 500px;
    width: 90%;
}
</style>

<div class="container">
    <div class="premium-hero">
        <h1 style="font-size: 2.5rem; margin-bottom: 1rem;">Passez à Loove Premium</h1>
        <p style="font-size: 1.2rem;">Débloquez toutes les fonctionnalités et maximisez vos chances de trouver l'amour</p>
    </div>

    <?php if($success): ?>
        <div class="alert alert-success">✓ <?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <?php if($error): ?>
        <div class="alert alert-error">✗ <?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="premium-plans">
        <div class="plan-card">
            <h3>Hebdomadaire</h3>
            <div class="plan-price">9,99€</div>
            <p>par semaine</p>
            <ul class="plan-features">
                <li>✓ Likes illimités</li>
                <li>✓ 5 Super Likes par jour</li>
                <li>✓ Voir qui vous aime</li>
            </ul>
            <button class="btn-premium" onclick="openPaymentModal('weekly', '9,99€')">
                Choisir ce plan
            </button>
        </div>

        <div class="plan-card featured">
            <div style="background: #FFD700; color: #2d3748; padding: 0.5rem; border-radius: 10px; margin-bottom: 1rem; font-weight: bold;">
                ⭐ PLUS POPULAIRE
            </div>
            <h3>Mensuel</h3>
            <div class="plan-price">29,99€</div>
            <p>par mois</p>
            <ul class="plan-features">
                <li>✓ Toutes les fonctionnalités hebdo</li>
                <li>✓ 1 Boost par mois</li>
                <li>✓ Retour en arrière</li>
                <li>✓ Badge Premium</li>
            </ul>
            <button class="btn-premium" onclick="openPaymentModal('monthly', '29,99€')">
                Choisir ce plan
            </button>
        </div>

        <div class="plan-card">
            <h3>Annuel</h3>
            <div class="plan-price">299,99€</div>
            <p>par an</p>
            <ul class="plan-features">
                <li>✓ Toutes les fonctionnalités</li>
                <li>✓ Économisez 60€</li>
                <li>✓ Boosts illimités</li>
                <li>✓ Support prioritaire</li>
            </ul>
            <button class="btn-premium" onclick="openPaymentModal('yearly', '299,99€')">
                Choisir ce plan
            </button>
        </div>
    </div>

    <!-- Modal de paiement -->
    <div id="paymentModal" class="payment-modal">
        <div class="payment-form">
            <h3>Finaliser votre achat</h3>
            <p id="selectedPlan"></p>
            
            <form method="POST" action="/loove/public/premium/purchase">
                <input type="hidden" id="planType" name="plan_type" value="">
                <input type="hidden" name="payment_method" value="card">
                
                <div class="form-group">
                    <label>Numéro de carte</label>
                    <input type="text" name="card_number" placeholder="4111 1111 1111 1111" required>
                    <small>Utilisez une carte de test: 4111 1111 1111 1111</small>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label>Date d'expiration</label>
                        <input type="text" name="card_expiry" placeholder="MM/AA" required>
                    </div>
                    <div class="form-group">
                        <label>CVV</label>
                        <input type="text" name="card_cvv" placeholder="123" required>
                    </div>
                </div>
                
                <button type="submit" class="btn-premium">
                    Confirmer le paiement
                </button>
                <button type="button" onclick="closePaymentModal()" style="background: #f8f9fa; color: #2d3748; margin-top: 1rem;">
                    Annuler
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function openPaymentModal(planType, price) {
    document.getElementById('paymentModal').style.display = 'flex';
    document.getElementById('planType').value = planType;
    document.getElementById('selectedPlan').textContent = `Plan ${planType} - ${price}`;
}

function closePaymentModal() {
    document.getElementById('paymentModal').style.display = 'none';
}
</script>

<?php include BASE_PATH . '/app/views/layout/footer.php'; ?>
