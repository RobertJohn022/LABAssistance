//login.php
var register = document.getElementById("register")
var login = document.getElementById("login");
var passRecovery = document.getElementById("passRecovery");

function showLogin() {
    register.style.display = 'none';
    login.style.display = 'block';
    passRecovery.style.display = 'none';
}
function showRegister() {
    login.style.display = 'none';
    register.style.display = 'block';
    passRecovery.style.display = 'none';
}
function showPassRecovery() {
    register.style.display = 'none';
    login.style.display = 'none';
    passRecovery.style.display = 'block';
}

//dashboard.php
var newOrder = document.getElementById("newOrder")

function showNewOrder(){
    newOrder.style.display = 'block';
}
function hideNewOrder(){
    newOrder.style.display = 'none';
}