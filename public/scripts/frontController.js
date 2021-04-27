//function to check the equality of the two password in signup form
function checkEqualityOnPasswords(){
    var pass = document.getElementById('password').value;
    var vpass = document.getElementById('confirm_password').value;
    if (vpass.length > 0 ) {
      if( pass != vpass) { 
        document.getElementById('confirm_password').className = "form-control bg-danger text-white";
        document.getElementById('validate').disabled = true;
      } else { 
        document.getElementById('confirm_password').className = "form-control bg-success text-white";
        document.getElementById('validate').disabled = false;
      }
    }
  }

  //Function Regex on the password with different parameters
  function regexOnPassword() {
    var pass = document.getElementById('password').value;
    var error = "";
    var valid = true;
    if (pass.length < 8) {
      error += "Mot de passe trop petit (< 8 caractères) !<br>";
      valid = false;
    }
    if (pass.length > 20) {
      error += "Mot de passe trop long (> 20 caractères) !<br>";
      valid = false;
    }
    if (!/\d/.test(pass)) {
      error += "Doit contenir au moins un chiffre !<br>";
      valid = false;
    }
    if (!/[a-z]/.test(pass)) {
      error += "Doit contenir au moins une minuscule !<br>";
      valid = false;
    }
    if (!/[A-Z]/.test(pass)) {
      error += "Doit contenir au moins une majuscule !<br>";
      valid = false;
    }
    if (!/\W/.test(pass)) {
      error += "Doit contenir au moins un caractère spécial (@,#,...) !";
      valid = false;
    }
    if (valid === false) {
      document.getElementById("validate").disabled = true;
      document.getElementById("confirm_password").disabled = true;
    }
    else {
      error = "Mot de passe sécurisé !";
      document.getElementById("confirm_password").disabled = false;
  
    }
    document.getElementById('regexVerif').innerHTML = error;
  }
