<footer class="dolci-footer">
    <div class="dolci-container dolci-footer-grid">
        <div>
            <h3>thedolci</h3>
            <p>Premium tiramisu cloud kitchen. Freshly crafted daily with authentic Italian character.</p>
        </div>

        <div>
            <h4>Quick Links</h4>
            <ul class="dolci-footer-links">
                <li><a href="{{ route('thedolci.shop') }}">Shop</a></li>
                <li><a href="{{ route('thedolci.seasonal') }}">Seasonal</a></li>
                <li><a href="{{ route('thedolci.faq') }}">FAQ</a></li>
                <li><a href="{{ route('contact') }}">Contact</a></li>
                <li><a href="{{ route('thedolci.track-order') }}">Track Order</a></li>
            </ul>
        </div>

        <div>
            <h4>Get Offers</h4>
            <p>Subscribe and get first access to limited flavor drops and discount codes.</p>
            <form action="{{ route('thedolci.subscribe') }}" method="POST" class="dolci-subscribe-form">
                @csrf
                <input type="email" name="email" placeholder="you@example.com" required>
                <button type="submit">Subscribe</button>
            </form>
        </div>
    </div>
    <div class="dolci-footer-bottom">
        <p>{{ now()->year }} thedolci. All rights reserved.</p>
    </div>
</footer>

