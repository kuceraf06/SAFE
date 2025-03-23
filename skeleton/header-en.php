<div class="top-bar">
        <div class="top-left">
            <p class="miners-logo">Miners Kladno Baseball & Softball</p>
        </div>
        <div class="top-right">
            <a href="https://softball.cz" target="_blank"><img src="/images/softballczechWebWhite.png" alt="softLogo"></a>
            <a href="https://baseball.cz" target="_blank"><img class="second-image" src="/images/baseballczechWebWhite.png" alt="baseLogo"></a>
            <a href="https://www.flickr.com/photos/201375961@N07/" target="_blank"><i class='bx bxl-flickr-square'></i></a>
            <a href="https://www.facebook.com/minerskladno" target="_blank"><i class='bx bxl-facebook-square'></i></a>
            <a href="https://www.instagram.com/minerskladno/" target="_blank"><i class='bx bxl-instagram-alt'></i></a>
        </div>
    </div>
    <header class="header mobile-header">
        <a href="../"><img id="logo" src="/images/SAFE.png"></a>
            <input type="checkbox" id="check">
            <label for="check" class="icons">
                <i class="bx bx-menu" id="menu-icon"></i>
                <i class="bx bx-x" id="close-icon"></i>
            </label>
            <script>
             document.addEventListener("DOMContentLoaded", function() {
                var aboutToggle = document.getElementById('aboutButton');
                var aboutIcon = document.querySelector('#aboutButton .toggleIcon');
                var rosterToggle = document.getElementById('rosterButton');
                var rosterIcon = document.querySelector('#rosterButton .toggleIcon');
        
                aboutToggle.addEventListener('click', function() {
                    toggleDropdown(this, aboutIcon);
                });
        
                rosterToggle?.addEventListener('click', function() {
                    toggleDropdown(this, rosterIcon);
                });

                aboutIcon.addEventListener('click', function(event) {
                    toggleDropdown(aboutToggle, aboutIcon);
                    event.stopPropagation();
                });

                rosterIcon?.addEventListener('click', function(event) {
                    toggleDropdown(rosterToggle, rosterIcon);
                    event.stopPropagation();
                });
        
                function toggleDropdown(toggle, icon) {
                    var dropdownContent = toggle.nextElementSibling;
                    var isOpen = dropdownContent.style.display === 'block';
        
                    closeAllDropdowns();

                    dropdownContent.style.display = isOpen ? 'none' : 'block';

                    if (icon) {
                        icon.classList.toggle('rotate-90', !isOpen);
                    }

                    setTimeout(function() {
                        icon.style.transition = 'transform 0.3s ease';
                        dropdownContent.style.transition = isOpen ? 'opacity 0.3s' : 'opacity 0.5s';
                    }, 50);
                }
        
                function closeAllDropdowns() {
                    var dropdownContents = document.querySelectorAll('.dropdown-content');
                    dropdownContents.forEach(function(content) {
                        content.style.display = 'none';
                    });
        
                    [aboutIcon, rosterIcon].forEach(function(icon) {
                        if (icon) {
                            icon.classList.remove('rotate-90');
                        }
                    });
                }
                document.addEventListener('click', function(event) {
                    if (!event.target.matches('.dropdown-toggle')) {
                        closeAllDropdowns();
                        [aboutIcon, rosterIcon].forEach(function(icon) {
                            if (icon) {
                                icon.classList.remove('rotate-90');
                            }
                        });
                    }
                });
            });    
        </script>