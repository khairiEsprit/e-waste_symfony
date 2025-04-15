document.addEventListener('DOMContentLoaded', function () {
   function togglePassword(eyeIcon, inputId) {
       const passwordInput = document.getElementById(inputId);
       if (passwordInput) {
           passwordInput.type = passwordInput.type === 'password' ? 'text' : 'password';
           eyeIcon.classList.toggle('ri-eye-line');
           eyeIcon.classList.toggle('ri-eye-off-line');
       }
   }

   document.addEventListener('click', function (e) {
       if (e.target.classList.contains('login__eye') || e.target.classList.contains('toggle-password')) {
           let inputId;
           // Handle login page
           if (e.target.id === 'login-eye') {
               inputId = 'login-pass';
           }
           // Handle register page
           else if (e.target.id === 'register-eye') {
               inputId = 'register-pass';
           } else if (e.target.id === 'register-eye-confirm') {
               inputId = 'register-pass-confirm';
           }
           // Fallback to data-target (for register page with inline script)
           else if (e.target.hasAttribute('data-target')) {
               inputId = e.target.getAttribute('data-target');
           }

           if (inputId) {
               togglePassword(e.target, inputId);
           }
       }
   });
});