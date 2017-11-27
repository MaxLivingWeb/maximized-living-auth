<header class="headerNavBar">
    <div class="navContainer">
        <a class="navLogo" href="/" title="Max Living">
            <img src="/images/logos/ML-extended-logo.svg" alt="Max Living"/>
        </a>
        <nav class="navLinks-primary">
            <ul class="navLinks">
                <li><a class="active navLink" href="#" title="The 5 Essentials">The 5 Essentials</a></li>
                <li><a class="navLink" href="#" title="Vitamins &amp; Supplements">Vitamins &amp; Supplements</a></li>
                <li><a class="navLink" href="#" title="Fitness">Fitness</a></li>
                <li><a class="navLink" href="#" title="Spinal Correction">Spinal Correction</a></li>
                <li><a class="navLink" href="#" title="Books">Books</a></li>
            </ul>
            <ul class="mobileMenuBottomNavLinks">
                <li>
                    @if(Auth::check())
                        <a class="navLinkWithIcon" href="{{ route('logout') }}" title="Logout">
                            <span class="icon-account"></span>
                            <span class="navLinkText">Logout</span>
                        </a>
                    @else
                        <a class="navLinkWithIcon" href="{{ route('login') }}" title="Login">
                            <span class="icon-account"></span>
                            <span class="navLinkText">Login</span>
                        </a>
                    @endif
                </li>
            </ul>
        </nav>
        <nav class="navLinks-secondary">
            <ul class="navLinks">
                <li class="hideForMobileMenu">
                    @if(Auth::check())
                        <a class="navLinkWithIcon" href="{{ route('logout') }}" title="Logout">
                            <span class="icon-account"></span>
                            <span class="navLinkText">Logout</span>
                        </a>
                    @else
                        <a class="navLinkWithIcon" href="{{ route('login') }}" title="Login">
                            <span class="icon-account"></span>
                            <span class="navLinkText">Login</span>
                        </a>
                    @endif
                </li>
                <li>
                    <a class="navLinkWithIcon" href="#" title="Search">
                        <span class="icon-search"></span>
                        <span class="invisible">Search</span>
                    </a>
                </li>
                <li>
                    <a class="navLinkWithIcon cart-navLink" href="#" title="View Cart">
                        <span class="icon-cart"></span>
                        <span class="cart-navCounter">2</span>
                    </a>
                </li>
                <li class="mobileMenuButtonContainer">
                    <button class="mobileMenuButton" title="Menu">
                        <span class="line-1"></span>
                        <span class="line-2"></span>
                        <span class="line-3"></span>
                        <span class="invisible">Menu</span>
                    </button>
                </li>
            </ul>
        </nav>
    </div>
</header>
