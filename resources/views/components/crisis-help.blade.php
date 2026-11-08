<aside {{ $attributes->class('crisis') }} aria-labelledby="bantuan-judul">
    <h2 id="bantuan-judul">Butuh Bantuan Segera?</h2>
    <p>Ruang Pulang bukan layanan darurat. Kalau kamu atau orang di dekatmu dalam bahaya:</p>
    <ul>
        <li>Hubungi {{ config('community.crisis.name') }} di <a href="{{ config('community.crisis.phone_href') }}">{{ config('community.crisis.phone_label') }}</a>, gratis dari Kementerian Kesehatan.</li>
        <li>Kunjungi <a href="{{ config('community.crisis.url') }}" target="_blank" rel="noopener">healing119.id</a>.</li>
        <li>Datang ke IGD rumah sakit terdekat.</li>
    </ul>
</aside>
