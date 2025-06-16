   // Get modal elements
   const modalOverlay = document.getElementById('modalOverlay');
   const openModalButton = document.getElementById('openModalButton');
   const closeModalButton = document.getElementById('closeModalButton');
   
   // Open modal when the button is clicked
   openModalButton.addEventListener('click', () => {
       modalOverlay.style.display = 'block';
   });
   
   // Close modal when the close button is clicked
   closeModalButton.addEventListener('click', () => {
       modalOverlay.style.display = 'none';
   });
   
   // Close modal when clicking outside of the modal content
   window.addEventListener('click', (event) => {
       if (event.target === modalOverlay) {
           modalOverlay.style.display = 'none';
       }
   });
   