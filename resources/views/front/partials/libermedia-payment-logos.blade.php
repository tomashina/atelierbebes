<section class="lm-footer-payments" aria-label="Sigurno kartično plaćanje">
    <p>Sigurno plaćanje</p>
    <div class="lm-footer-payment-logos">
        <a class="lm-footer-payment-logo lm-footer-payment-logo--gateway" href="https://www.wspay.info/" target="_blank" rel="noopener noreferrer" aria-label="Monri WSPay – Web Secure Payment Gateway">
            <img src="{{ asset('assets/payments/wspay.svg') }}" alt="Monri WSPay" width="118" height="50" loading="lazy">
        </a>
        @foreach (['visa-brand' => 'Visa', 'mastercard-brand' => 'Mastercard', 'maestro-brand' => 'Maestro', 'diners-brand' => 'Diners Club'] as $asset => $label)
            <span class="lm-footer-payment-logo"><img src="{{ asset('assets/payments/'.$asset.'.svg') }}" alt="{{ $label }}" width="76" height="32" loading="lazy"></span>
        @endforeach
    </div>
</section>
