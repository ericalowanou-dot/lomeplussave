// Sélection des éléments
const modalButton = document.getElementById('megaphone-button');
const modalOverlay = document.getElementById('modal-overlay');
const modalStep1 = document.getElementById('modal-step1');
const closeButton = modalStep1.querySelector('.close');

// Ouvrir le modal
modalButton.addEventListener('click', () => {
    modalOverlay.style.display = 'block';
    modalStep1.style.display = 'block';
});

// Fermer le modal
closeButton.addEventListener('click', () => {
    modalOverlay.style.display = 'none';
    modalStep1.style.display = 'none';
});

modalOverlay.addEventListener('click', () => {
    modalOverlay.style.display = 'none';
    modalStep1.style.display = 'none';
});
