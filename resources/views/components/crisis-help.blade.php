<aside {{ $attributes->class('crisis') }} aria-labelledby="bantuan-judul">
    <h2 id="bantuan-judul">{{ __('Butuh Bantuan Segera?') }}</h2>
    <p>{{ __('Ruang Pulang bukan layanan darurat. Kalau kamu atau orang di dekatmu dalam bahaya:') }}</p>
    <ul>
        <li>{{ __('Hubungi :name di', ['name' => config('community.crisis.name')]) }} <a href="{{ config('community.crisis.phone_href') }}">{{ config('community.crisis.phone_label') }}</a>, {{ __('gratis dari Kementerian Kesehatan.') }}</li>
        <li>{{ __('Kunjungi') }} <a href="{{ config('community.crisis.url') }}" target="_blank" rel="noopener">healing119.id</a>.</li>
        <li>{{ __('Datang ke IGD rumah sakit terdekat.') }}</li>
    </ul>
</aside>
