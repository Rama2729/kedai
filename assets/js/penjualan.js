
(function () {
    let keranjang = [];

    const produkGrid       = document.getElementById('produkGrid');
    const keranjangList    = document.getElementById('keranjangList');
    const keranjangKosong  = document.getElementById('keranjangKosong');
    const keranjangTotalEl = document.getElementById('keranjangTotal');
    const jumlahBayarInput = document.getElementById('jumlahBayar');
    const kembalianText    = document.getElementById('kembalianText');
    const btnBayar         = document.getElementById('btnBayar');
    const btnKosongkan     = document.getElementById('btnKosongkan');

    if (!produkGrid) return;

    document.querySelectorAll('.filter-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            const kategori = btn.dataset.kategori;
            document.querySelectorAll('.produk-card').forEach(function (card) {
                if (kategori === 'semua' || card.dataset.kategori === kategori) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });

    produkGrid.addEventListener('click', function (e) {
        const card = e.target.closest('.produk-card');
        if (!card) return;

        const id    = card.dataset.id;
        const nama  = card.dataset.nama;
        const harga = parseFloat(card.dataset.harga);

        const existing = keranjang.find(item => item.id === id);
        if (existing) {
            existing.qty += 1;
        } else {
            keranjang.push({ id, nama, harga, qty: 1 });
        }
        renderKeranjang();
    });

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
                <div>
                    <div>${item.nama}</div>
                    <div style="color:#888; font-size:0.8rem;">${formatRupiah(item.harga)} x ${item.qty}</div>
                </div>
                <div class="qty-control">
                    <button type="button" data-action="kurang" data-index="${index}">-</button>
                    <span>${item.qty}</span>
                    <button type="button" data-action="tambah" data-index="${index}">+</button>
                    <button type="button" data-action="hapus" data-index="${index}" style="color:var(--color-danger); border:none; background:none; cursor:pointer; margin-left:4px;">&times;</button>
                </div>
            `;
            keranjangList.appendChild(row);
        });

        keranjangTotalEl.textContent = formatRupiah(total);
        hitungKembalian();
    }

    keranjangList.addEventListener('click', function (e) {
        const btn = e.target.closest('button[data-action]');
        if (!btn) return;

        const index = parseInt(btn.dataset.index, 10);
        const action = btn.dataset.action;

        if (action === 'tambah') {
            keranjang[index].qty += 1;
        } else if (action === 'kurang') {
            keranjang[index].qty -= 1;
            if (keranjang[index].qty <= 0) {
                keranjang.splice(index, 1);
            }
        } else if (action === 'hapus') {
            keranjang.splice(index, 1);
        }

        renderKeranjang();
    });

    function getTotalKeranjang() {
        return keranjang.reduce((sum, item) => sum + (item.harga * item.qty), 0);
    }

    function hitungKembalian() {
        const total = getTotalKeranjang();
        const bayar = parseFloat(jumlahBayarInput.value) || 0;
        const kembalian = bayar - total;
        kembalianText.textContent = formatRupiah(kembalian < 0 ? 0 : kembalian);
        kembalianText.style.color = kembalian < 0 ? 'var(--color-danger)' : 'var(--color-success)';
    }

    jumlahBayarInput.addEventListener('input', hitungKembalian);

    btnKosongkan.addEventListener('click', function () {
        if (keranjang.length === 0) return;
        if (confirm('Kosongkan semua item di keranjang?')) {
            keranjang = [];
            renderKeranjang();
        }
    });

    btnBayar.addEventListener('click', function () {
        if (keranjang.length === 0) {
            alert('Keranjang masih kosong.');
            return;
        }

        const total = getTotalKeranjang();
        const bayar = parseFloat(jumlahBayarInput.value) || 0;

        if (bayar < total) {
            alert('Jumlah bayar kurang dari total belanja.');
            return;
        }

        const payload = {
            items: keranjang.map(item => ({ menu_id: item.id, qty: item.qty })),
            metode_bayar: document.getElementById('metodeBayar').value,
            bayar: bayar,
            keterangan: document.getElementById('keterangan').value
        };

        btnBayar.disabled = true;
        btnBayar.textContent = 'Memproses...';

        fetch('simpan.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                alert('Transaksi berhasil disimpan!\nNo. Transaksi: ' + data.no_transaksi);
                window.location.reload();
            } else {
                alert('Gagal menyimpan transaksi: ' + (data.message || 'Terjadi kesalahan.'));
                btnBayar.disabled = false;
                btnBayar.textContent = 'Proses Bayar';
            }
        })
        .catch(err => {
            alert('Terjadi kesalahan koneksi: ' + err.message);
            btnBayar.disabled = false;
            btnBayar.textContent = 'Proses Bayar';
        });
    });

})();
