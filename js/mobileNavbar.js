function openNav() {

    var x = document.getElementById('nav-box');
    var y = document.getElementById('navBtn');

    x.classList.add('slideIn');
    x.classList.remove('slideOut');
    y.classList.add('hide');

}

function closeNav() {
    
    var x = document.getElementById('nav-box');
    var y = document.getElementById('navBtn');

    x.classList.remove('slideIn');
    x.classList.add('slideOut');
    y.classList.remove('hide');

}