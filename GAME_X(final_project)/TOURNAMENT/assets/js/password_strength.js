const passwordInput = document.getElementById('password');
const strengthBar = document.getElementById('strength-bar');
const strengthText = document.getElementById('strength-text');

passwordInput.addEventListener('input', () => {
  const val = passwordInput.value;
  let strength = 0;

  if (val.match(/[a-z]+/)) strength++;
  if (val.match(/[A-Z]+/)) strength++;
  if (val.match(/[0-9]+/)) strength++;
  if (val.match(/[$@#&!]+/)) strength++;
  if (val.length >= 8) strength++;

  let color, text;
  switch (strength) {
    case 0:
    case 1:
      color = "red"; text = "Very Weak"; break;
    case 2:
      color = "orange"; text = "Weak"; break;
    case 3:
      color = "gold"; text = "Moderate"; break;
    case 4:
      color = "limegreen"; text = "Strong"; break;
    case 5:
      color = "green"; text = "Excellent"; break;
  }

  strengthBar.style.width = (strength * 20) + '%';
  strengthBar.style.backgroundColor = color;
  strengthText.textContent = text;
});
