// ===== GLOBAL HIDDEN INPUTS =====
const serviceInput = document.getElementById('serviceInput');
const barberInput = document.getElementById('barberInput');
const paymentInput = document.getElementById('paymentInput');

// ===== STEP NAVIGATION =====
function nextStep(n) {
    for (let i = 1; i <= 4; i++) {
        document.getElementById('step' + i).classList.add('hidden');
        document.getElementById('s' + i).classList.remove('active');
    }
    document.getElementById('step' + n).classList.remove('hidden');
    document.getElementById('s' + n).classList.add('active');
}

// ===== SELECT SERVICE =====
function selectService(value, event) {
    serviceInput.value = value; // set hidden input
    document.querySelectorAll('.service').forEach(s => s.classList.remove('active'));
    event.currentTarget.classList.add('active');
}

// ===== SELECT BARBER =====
function selectBarber(value, event) {
    barberInput.value = value; // set hidden input
    document.querySelectorAll('.barber').forEach(b => b.classList.remove('active'));
    event.currentTarget.classList.add('active');
}

// ===== SELECT PAYMENT =====
function selectPayment(value, event) {
    paymentInput.value = value; // set hidden input
    document.querySelectorAll('.payment').forEach(p => p.classList.remove('active'));
    event.currentTarget.classList.add('active');
}

// ===== NEXT BUTTON VALIDATION =====
function canProceed(step) {
    if(step === 2 && !serviceInput.value) return false;
    if(step === 3 && !barberInput.value) return false;
    if(step === 4) {
        const date = document.querySelector('input[name="appointment_date"]').value;
        const time = document.querySelector('input[name="appointment_time"]').value;
        if(!date || !time) return false;
    }
    return true;
}

// ===== ATTACH NEXT BUTTON EVENTS =====
document.querySelectorAll('button[type="button"]').forEach(button => {
    button.addEventListener('click', function() {
        const next = parseInt(this.getAttribute('data-next'));
        if(!canProceed(next)){
            alert('Please complete this step first!');
            return;
        }
        nextStep(next);
    });
});
