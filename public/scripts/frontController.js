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
      error += "Password too small (< 8 characters) !<br>";
      valid = false;
    }
    if (pass.length > 20) {
      error += "Password too long (> 20 characters) !<br>";
      valid = false;
    }
    if (!/\d/.test(pass)) {
      error += "Must contain at least one number !<br>";
      valid = false;
    }
    if (!/[a-z]/.test(pass)) {
      error += "Must contain at least one lowercase letter !<br>";
      valid = false;
    }
    if (!/[A-Z]/.test(pass)) {
      error += "Must contain at least one capital letter !<br>";
      valid = false;
    }
    if (!/\W/.test(pass)) {
      error += "Must contain at least one special character (#,@,...) !";
      valid = false;
    }
    if (valid === false) {
      document.getElementById("validate").disabled = true;
      document.getElementById("confirm_password").disabled = true;
    }
    else {
      error = "Secure password !";
      document.getElementById("confirm_password").disabled = false;
  
    }
    document.getElementById('regexVerif').innerHTML = error;
  }
