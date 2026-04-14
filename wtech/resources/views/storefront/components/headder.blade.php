<header class="border-wrap">
    <div class="header-top">
        <div class="container-xl d-flex justify-content-between align-items-center flex-wrap gap-2">
            <nav>
                <ul class="list-unstyled d-flex align-items-center mb-0 small m-0 top-links">
                    <li><a href="#">Articles &amp; Support</a></li>
                    <li class="divider">|</li>
                    <li><a href="#">Contact Us</a></li>
                    <li class="divider">|</li>
                    <li><a href="#">Track Order</a></li>
                </ul>
            </nav>

            <div class="d-flex gap-3 align-items-center">
                @guest
                    <a href="{{ route('login') }}" class="small register-link">Login</a>
                    <a href="{{ route('register') }}" class="small register-link">Register</a>
                @endguest

                @auth
                    <span class="small register-link">
                        {{ Auth::user()->first_name ?? Auth::user()->email }}
                        
                    </span>

                    <form method="POST" action="{{ route('logout') }}" class="m-0">
                        @csrf
                        <button type="submit" class="small register-link border-0 bg-transparent p-0 text-white">
                            Logout
                        </button>
                    </form>
                @endauth
            </div>
        </div>
    </div>

    <div class="border-bottom-wrap">
        <div class="container-xl py-2">
            <div class="row g-3 align-items-center">
                <div class="col-12 col-md-2">
                    <a class="logo-box text-decoration-none text-reset" href="{{ url('/') }}">ElectroHub</a>
                </div>

                <div class="col-12 col-md-7">
                    <form method="GET" action="{{ url('/products') }}">
                        <div class="input-group">
                            <span class="input-group-text" style="background: transparent; border: 1px solid var(--border); border-right: none;">
                                <i class="fas fa-search"></i>
                            </span>
                            <input
                                type="search"
                                name="q"
                                class="form-control neutral-search"
                                placeholder="Search for products..."
                                aria-label="Search for products"
                                style="border-left: none;"
                                value="{{ request('search') }}"
                            >
                        </div>
                    </form>
                </div>

                <div class="col-12 col-md-3 d-flex gap-2 justify-content-md-end">
                    <a class="btn cart-btn" href="{{ url('/cart') }}">
                        <i class="fas fa-shopping-cart"></i>
                        Cart
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div>
        <nav class="container-xl py-1">
            <ul class="list-unstyled d-flex flex-wrap gap-4 mb-0 category-links">
                <li>
                    <a href="{{ url('/') }}">
                        <i class="fas fa-home"></i> Home
                    </a>
                </li>
                <li>
                    <a href="{{ route('products.index') }}">
                        <i class="fas fa-laptop"></i> Products
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</header>