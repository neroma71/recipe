let myHeader = document.querySelector('header');
let banner = document.querySelector('.banner');
    window.addEventListener('scroll', () => {
        if (window.scrollY > 50) {
            myHeader.classList.add('myhead');
            banner.style.top = '150px';
        } else {
            myHeader.classList.remove('myhead');
            banner.style.top = '250px';
        }
    });
