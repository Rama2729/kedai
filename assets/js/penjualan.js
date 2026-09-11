(function () {

    let keranjang = [];

    const produkGrid = document.getElementById('produkGrid');
    const keranjangList = document.getElementById('keranjangList');
    const keranjangKosong = document.getElementById('keranjangKosong');
    const keranjangTotalEl = document.getElementById('keranjangTotal');
    const jumlahBayarInput = document.getElementById('jumlahBayar');
    const kembalianText = document.getElementById('kembalianText');
    const btnBayar = document.getElementById('btnBayar');
    const btnKosongkan = document.getElementById('btnKosongkan');
    const metodeBayar = document.getElementById('metodeBayar');
    const keterangan = document.getElementById('keterangan');

    if (!produkGrid) {
        return;
    }

    document.querySelectorAll('.filter-btn').forEach(function (btn) {

        btn.addEventListener('click', function () {

            document.querySelectorAll('.filter-btn')
                .forEach(item => item.classList.remove('active'));

            btn.classList.add('active');

            const kategori = btn.dataset.kategori;

            document.querySelectorAll('.produk-card')
                .forEach(function (card) {

                    if (
                        kategori === 'semua' ||
                        card.dataset.kategori === kategori
                    ) {
                        card.style.display = '';
                    } else {
                        card.style.display = 'none';
                    }
                });
        });
    });

    produkGrid.addEventListener('click', function (e) {

        const card = e.target.closest('.produk-card');

        if (!card) {
            return;
        }

        const id = card.dataset.id;
        const nama = card.dataset.nama;
        const harga = parseFloat(card.dataset.harga);

        tambahProduk(id, nama, harga);

        card.classList.add('selected');

        setTimeout(function () {
            card.classList.remove('selected');
        }, 300);
    });

    function tambahProduk(id, nama, harga) {

        const existing = keranjang.find(item => item.id === id);

        if (existing) {
            existing.qty += 1;
        } else {
            keranjang.push({
                id: id,
                nama: nama,
                harga: harga,
                qty: 1
            });
        }

        renderKeranjang();
    }

    function renderKeranjang() {

        keranjangList.innerHTML = '';

        if (keranjang.length === 0) {

            keranjangList.appendChild(keranjangKosong);

            keranjangTotalEl.textContent = formatRupiah(0);

            hitungKembalian();

            return;
        }

        let total = 0;

        keranjang.forEach(function (item, index) {

            const subtotal = item.harga * item.qty;

            total += subtotal;

            const row = document.createElement('div');

            row.className = 'keranjang-item';

            row.innerHTML = `
                <div class="item-info">
                    <div class="item-name">
                        ${item.nama}
                    </div>

                    <div class="item-price">
                        ${formatRupiah(item.harga)} × ${item.qty}
                    </div>
                </div>

                <div class="qty-control">

                    <button
                        type="button"
                        data-action="kurang"
                        data-index="${index}">
                        −
                    </button>

                    <span>${item.qty}</span>

                    <button
                        type="button"
                        data-action="tambah"
                        data-index="${index}">
                        +
                    </button>

                    <button
                        type="button"
                        data-action="hapus"
                        data-index="${index}"
                        class="hapus-item">
                        ×
                    </button>

                </div>
            `;

            keranjangList.appendChild(row);
        });

        keranjangTotalEl.textContent = formatRupiah(total);

        hitungKembalian();
    }

    keranjangList.addEventListener('click', function (e) {

        const btn = e.target.closest('button[data-action]');

        if (!btn) {
            return;
        }

        const index = parseInt(btn.dataset.index);
        const action = btn.dataset.action;

        if (action === 'tambah') {
            keranjang[index].qty += 1;
        }

        if (action === 'kurang') {

            keranjang[index].qty -= 1;

            if (keranjang[index].qty <= 0) {
                keranjang.splice(index, 1);
            }
        }

        if (action === 'hapus') {
            keranjang.splice(index, 1);
        }

        renderKeranjang();
    });

    function getTotalKeranjang() {

        return keranjang.reduce(function (total, item) {

            return total + (item.harga * item.qty);

        }, 0);
    }

    function hitungKembalian() {

        if (!jumlahBayarInput || !kembalianText) {
            return;
        }

        const total = getTotalKeranjang();

        const bayar =
            parseFloat(jumlahBayarInput.value) || 0;

        const kembalian = bayar - total;

        if (kembalian < 0) {

            kembalianText.textContent =
                'Kurang ' +
                formatRupiah(Math.abs(kembalian));

            kembalianText.style.color =
                'var(--color-danger)';

        } else {

            kembalianText.textContent =
                formatRupiah(kembalian);

            kembalianText.style.color =
                'var(--color-success)';
        }
    }

    if (jumlahBayarInput) {
        jumlahBayarInput.addEventListener(
            'input',
            hitungKembalian
        );
    }

    if (btnKosongkan) {

        btnKosongkan.addEventListener('click', function () {

            if (keranjang.length === 0) {
                return;
            }

            if (confirm('Kosongkan keranjang?')) {

                keranjang = [];

                renderKeranjang();
            }
        });
    }

    if (btnBayar) {

        btnBayar.addEventListener('click', function () {

            if (keranjang.length === 0) {
                alert('Keranjang masih kosong.');
                return;
            }

            const total = getTotalKeranjang();

            const bayar =
                parseFloat(jumlahBayarInput.value) || 0;

            if (bayar < total) {
                alert('Jumlah pembayaran kurang.');
                return;
            }

            const payload = {

                items: keranjang.map(function (item) {

                    return {
                        menu_id: item.id,
                        qty: item.qty
                    };

                }),

                metode_bayar:
                    metodeBayar ? metodeBayar.value : 'Tunai',

                bayar: bayar,

                keterangan:
                    keterangan ? keterangan.value : ''
            };

            btnBayar.disabled = true;
            btnBayar.innerHTML = 'Memproses...';

            fetch('simpan.php', {

                method: 'POST',

                headers: {
                    'Content-Type': 'application/json'
                },

                body: JSON.stringify(payload)

            })

            .then(response => response.json())

            .then(function (data) {

                if (data.status === 'success') {

                    alert(
                        'Transaksi berhasil\n\n' +
                        'No. Transaksi : ' +
                        data.no_transaksi
                    );

                    window.location.reload();

                } else {

                    alert(
                        data.message ||
                        'Gagal menyimpan transaksi.'
                    );

                    btnBayar.disabled = false;
                    btnBayar.innerHTML = 'Proses Bayar';
                }
            })

            .catch(function (error) {

                alert(
                    'Koneksi gagal : ' +
                    error.message
                );

                btnBayar.disabled = false;
                btnBayar.innerHTML = 'Proses Bayar';
            });
        });
    }

})();
