@extends('thedolci.layouts.store')

@section('title', 'Loyalty Rewards | thedolci')

@section('content')
<section class="dolci-section dolci-section-tight">
    <div class="dolci-container">
        <div class="dolci-section-head">
            <h1>thedolci Rewards</h1>
            <p>Earn points with every order and unlock premium perks.</p>
        </div>

        <div class="dolci-three-cols">
            <article>
                <h3>Bronze</h3>
                <p>0-149 points</p>
                <p>Birthday treat and members-only flavor alerts.</p>
            </article>
            <article>
                <h3>Silver</h3>
                <p>150-399 points</p>
                <p>Priority seasonal pre-order access and early drops.</p>
            </article>
            <article>
                <h3>Gold</h3>
                <p>400+ points</p>
                <p>Exclusive monthly offers and complimentary add-ons.</p>
            </article>
        </div>

        <div class="dolci-card-note">
            <p>You earn <strong>1 point for every $10 spent</strong>. Redeem points during checkout for discounts and perks.</p>
        </div>
    </div>
</section>
@endsection

