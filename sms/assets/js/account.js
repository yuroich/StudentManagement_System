// Manage Account page: show/hide password and show chosen photo name.
document.getElementById('pwToggle').addEventListener('click', function () {
  const input = document.getElementById('password');
  const hidden = input.type === 'password';
  input.type = hidden ? 'text' : 'password';
  this.textContent = hidden ? 'hide' : 'show';
});

document.getElementById('avatarInput').addEventListener('change', function () {
  document.getElementById('avatarName').textContent = this.files[0] ? this.files[0].name : '';
});
