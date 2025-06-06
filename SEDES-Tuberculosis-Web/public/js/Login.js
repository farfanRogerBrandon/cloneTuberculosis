const container = document.querySelector('.container');
const toRegister = document.getElementById('to-register');
const toLogin = document.getElementById('to-login');

toRegister.addEventListener('click', () => container.classList.add('active'));
toLogin.addEventListener('click', () => container.classList.remove('active'));