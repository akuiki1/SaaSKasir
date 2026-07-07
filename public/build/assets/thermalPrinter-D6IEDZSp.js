import{t as e}from"./createLucideIcon-CjvIwW-M.js";import{n as t,r as n}from"./format-C8HFEK_t.js";var r=e(`BluetoothIcon`,[[`path`,{d:`m7 7 10 10-5 5V2l5 5L7 17`,key:`1q5490`}]]);function i(e,r){let i=e.details.map(e=>{let r=e.harga*e.jumlah-e.subtotal;return`
                <tr>
                    <td>${e.nama_produk}</td>
                    <td class="text-right">${t(e.jumlah)}</td>
                    <td class="text-right">${n(e.harga)}</td>
                    <td class="text-right">${n(e.subtotal)}</td>
                </tr>
                ${r>=1?`<tr>
                    <td colspan="4" class="small diskon-note">Hemat ${n(r)}</td>
                </tr>`:``}
            `}).join(``),a=e.details.reduce((e,t)=>e+(t.nominal?Number(t.nominal):0),0),o=e.total_harga+a,s=Number(e.diskon??0);return`<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8" />
<title>Struk ${e.kode}</title>
<style>
    body { font-family: 'Courier New', Courier, monospace; font-size: 12px; color: #000; margin: 0; padding: 12px; }
    .receipt { width: 320px; }
    .receipt h1, .receipt h2, .receipt p { margin: 0; }
    .receipt h1 { font-size: 16px; margin-bottom: 8px; }
    .receipt .center { text-align: center; }
    .receipt .separator { border-top: 1px dashed #000; margin: 8px 0; }
    .receipt table { width: 100%; border-collapse: collapse; }
    .receipt td { padding: 2px 0; vertical-align: top; }
    .receipt .text-right { text-align: right; }
    .receipt .totals td { padding: 4px 0; }
    .receipt .small { font-size: 11px; }
    .receipt thead td { font-weight: bold; border-bottom: 1px solid #000; padding-bottom: 4px; }
    .receipt .diskon-note { padding: 0 0 4px; font-style: italic; }
</style>
</head>
<body>
<div class="receipt">
    <h1 class="center">${r}</h1>
    <p class="center small">Struk Transaksi</p>
    <div class="separator"></div>
    <p>Kode: ${e.kode}</p>
    <p>${e.tanggal} ${e.waktu}</p>
    <div class="separator"></div>
    <table>
        <thead>
            <tr>
                <td>Produk</td>
                <td class="text-right">Qty</td>
                <td class="text-right">Harga Asal</td>
                <td class="text-right">Subtotal</td>
            </tr>
        </thead>
        <tbody>
            ${i}
        </tbody>
    </table>
    <div class="separator"></div>
    <table class="totals">
        ${a>0?`<tr>
            <td>Penjualan / Fee</td>
            <td class="text-right">${n(e.total_harga)}</td>
        </tr>
        <tr>
            <td>Titipan layanan</td>
            <td class="text-right">${n(a)}</td>
        </tr>`:``}
        <tr>
            <td>Total</td>
            <td class="text-right">${n(o)}</td>
        </tr>
        <tr>
            <td>Bayar</td>
            <td class="text-right">${n(e.bayar)}</td>
        </tr>
        <tr>
            <td>Kembalian</td>
            <td class="text-right">${n(e.kembalian)}</td>
        </tr>
    </table>
    <div class="separator"></div>
    ${s>0?`<p class="center small">Anda hemat ${n(s)} dari promo.</p>`:``}
    <p class="center small">Terima kasih atas kunjungan Anda.</p>
</div>
</body>
</html>`}function a(e,t){if(typeof window>`u`)return;let n=window.open(``,`_blank`,`width=400,height=700`);if(!n){window.alert(`Tidak dapat membuka jendela cetak. Pastikan pop-up tidak diblokir.`);return}n.document.write(i(e,t)),n.document.close(),n.focus(),n.print()}var o=32,s=16,c=27,l=29,u=10,d=768,f=879,p=class{bytes=[];push(...e){for(let t of e)this.bytes.push(t);return this}init(){return this.push(c,64)}align(e){let t=e===`center`?1:e===`right`?2:0;return this.push(c,97,t)}bold(e){return this.push(c,69,+!!e)}double(e){return this.push(l,33,e?17:0)}text(e){return this.push(...m(e))}line(e=``){return this.text(e).push(u)}feed(e=1){return this.push(c,100,e)}cut(){return this.push(l,86,0)}build(){return Uint8Array.from(this.bytes)}};function m(e){let t=[];for(let n of e.normalize(`NFKD`)){let e=n.codePointAt(0)??63;e>=d&&e<=f||t.push(e<=127?e:63)}return t}function h(e,t,n=o){let r=n-e.length-t.length;if(r>=1)return e+` `.repeat(r)+t;let i=Math.max(0,n-t.length-1);return e.slice(0,i)+` `+t}function g(e,t=o){let n=[],r=``;for(let i of e.split(/\s+/).filter(Boolean)){let e=r?`${r} ${i}`:i;if(e.length<=t){r=e;continue}r&&n.push(r);let a=i;for(;a.length>t;)n.push(a.slice(0,t)),a=a.slice(t);r=a}return r&&n.push(r),n.length>0?n:[``]}function _(e,r){let i=new p,a=`-`.repeat(o);i.init(),i.align(`center`).bold(!0).double(!0);for(let e of g(r,s))i.line(e);i.double(!1).bold(!1),i.line(`Struk Transaksi`),i.align(`left`).line(a),i.line(`Kode: ${e.kode}`),i.line(`${e.tanggal} ${e.waktu}`),i.line(a);let c=0;for(let r of e.details){c+=r.nominal?Number(r.nominal):0;for(let e of g(r.nama_produk))i.line(e);let e=`  ${t(r.jumlah)} x ${n(r.harga)}`;i.line(h(e,n(r.subtotal)));let a=r.harga*r.jumlah-r.subtotal;a>=1&&i.line(`  Hemat ${n(a)}`)}i.line(a);let l=e.total_harga+c;c>0&&(i.line(h(`Penjualan / Fee`,n(e.total_harga))),i.line(h(`Titipan layanan`,n(c)))),i.bold(!0).line(h(`Total`,n(l))).bold(!1),i.line(h(`Bayar`,n(e.bayar))),i.line(h(`Kembalian`,n(e.kembalian))),i.line(a);let u=Number(e.diskon??0);return i.align(`center`),u>0&&i.line(`Anda hemat ${n(u)} dari promo.`),i.line(`Terima kasih atas`),i.line(`kunjungan Anda.`),i.feed(3).cut(),i.build()}var v=[{service:`000018f0-0000-1000-8000-00805f9b34fb`,characteristic:`00002af1-0000-1000-8000-00805f9b34fb`},{service:`0000ff00-0000-1000-8000-00805f9b34fb`,characteristic:`0000ff02-0000-1000-8000-00805f9b34fb`},{service:`0000ffe0-0000-1000-8000-00805f9b34fb`,characteristic:`0000ffe1-0000-1000-8000-00805f9b34fb`},{service:`49535343-fe7d-4ae5-8fa9-9fafd205e455`,characteristic:`49535343-8841-43f4-a8d4-ecbe34729bb3`}],y=v.map(e=>e.service),b=180,x=20,S=null,C=null;function w(){return typeof navigator<`u`&&!!navigator.bluetooth}function T(){return S?.gatt?.connected?S.name??`Printer`:null}async function E(){if(!w())throw Error(`Perangkat ini tidak mendukung cetak Bluetooth (butuh Chrome di Android/desktop & HTTPS).`);let e=await navigator.bluetooth.requestDevice({acceptAllDevices:!0,optionalServices:y});return e.addEventListener(`gattserverdisconnected`,()=>{C=null}),S=e,C=await k(e),e.name??`Printer`}async function D(e,t){await A(await O(),_(e,t))}async function O(){return C&&S?.gatt?.connected?C:S?(C=await k(S),C):(await E(),C)}async function k(e){if(!e.gatt)throw Error(`Printer tidak memiliki antarmuka GATT.`);let t=e.gatt.connected?e.gatt:await e.gatt.connect();for(let e of v)try{return await(await t.getPrimaryService(e.service)).getCharacteristic(e.characteristic)}catch{continue}let n=await t.getPrimaryServices();for(let e of n){let t=await e.getCharacteristics();for(let e of t)if(e.properties.write||e.properties.writeWithoutResponse)return e}throw Error(`Tidak menemukan karakteristik printer yang bisa ditulis.`)}async function A(e,t){let n=e.properties.writeWithoutResponse;for(let r=0;r<t.length;r+=b){let i=t.slice(r,r+b);n?await e.writeValueWithoutResponse(i):await e.writeValue(i),await j(x)}}function j(e){return new Promise(t=>{setTimeout(t,e)})}export{r as a,a as i,T as n,D as r,w as t};