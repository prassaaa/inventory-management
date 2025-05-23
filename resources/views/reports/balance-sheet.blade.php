@extends('layouts.app')

@section('title', 'Laporan Neraca')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-dark fw-bold">
                <i class="fas fa-balance-scale me-2 text-primary"></i> Laporan Neraca
            </h1>
            <p class="text-muted">Menampilkan posisi keuangan perusahaan pada tanggal tertentu</p>
        </div>
        <a href="{{ route('reports.finance') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 fw-bold text-primary">Filter</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('reports.balance-sheet') }}" method="GET">
                <div class="row align-items-end">
                    <div class="col-md-4">
                        <div class="form-group mb-0">
                            <label for="date" class="form-label">Tanggal Neraca</label>
                            <input type="date" class="form-control" id="date" name="date" value="{{ request('date', date('Y-m-d')) }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-0">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter me-1"></i> Tampilkan
                            </button>
                            <a href="{{ route('reports.balance-sheet') }}" class="btn btn-secondary">
                                <i class="fas fa-sync me-1"></i> Reset
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if(isset($estimatedBalance) && $estimatedBalance)
    <div class="alert alert-info">
        <i class="fas fa-info-circle me-2"></i>
        <strong>Informasi:</strong> Beberapa nilai dalam laporan ini diestimasi karena belum ada saldo awal yang diinput. Untuk hasil yang lebih akurat, silakan input saldo awal pada menu Keuangan > Saldo Awal.
    </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold text-primary">Laporan Neraca per {{ \Carbon\Carbon::parse($date)->format('d F Y') }}</h6>
            <button onclick="window.print()" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-print me-1"></i> Cetak
            </button>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- AKTIVA (KIRI) -->
                <div class="col-md-6">
                    <h5 class="text-primary fw-bold mb-3">AKTIVA</h5>

                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="fw-bold mb-0">Aktiva Lancar</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <tr>
                                    <td colspan="2"><strong>Kas & Setara Kas</strong></td>
                                    <td class="text-end"><strong>Rp {{ number_format($totalCashAndBank, 0, ',', '.') }}</strong></td>
                                </tr>
                                @if($cash > 0)
                                <tr>
                                    <td width="30px"></td>
                                    <td>Kas</td>
                                    <td class="text-end">Rp {{ number_format($cash, 0, ',', '.') }}</td>
                                </tr>
                                @endif
                                @if($bank1 > 0)
                                <tr>
                                    <td></td>
                                    <td>Bank BCA</td>
                                    <td class="text-end">Rp {{ number_format($bank1, 0, ',', '.') }}</td>
                                </tr>
                                @endif
                                @if($bank2 > 0)
                                <tr>
                                    <td></td>
                                    <td>Bank Mandiri</td>
                                    <td class="text-end">Rp {{ number_format($bank2, 0, ',', '.') }}</td>
                                </tr>
                                @endif
                                @if($accountsReceivable > 0)
                                <tr>
                                    <td colspan="2">Piutang Dagang</td>
                                    <td class="text-end">Rp {{ number_format($accountsReceivable, 0, ',', '.') }}</td>
                                </tr>
                                @endif
                                @if($inventory > 0)
                                <tr>
                                    <td colspan="2">Persediaan Barang</td>
                                    <td class="text-end">Rp {{ number_format($inventory, 0, ',', '.') }}</td>
                                </tr>
                                @endif
                                <tr class="fw-bold">
                                    <td colspan="2">Total Aktiva Lancar</td>
                                    <td class="text-end">Rp {{ number_format($totalCurrentAssets, 0, ',', '.') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($fixedAssets > 0 || $accumulatedDepreciation > 0)
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="fw-bold mb-0">Aktiva Tetap</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                @if($fixedAssets > 0)
                                <tr>
                                    <td>Aset Tetap</td>
                                    <td class="text-end">Rp {{ number_format($fixedAssets, 0, ',', '.') }}</td>
                                </tr>
                                @endif
                                @if($accumulatedDepreciation > 0)
                                <tr>
                                    <td>Akumulasi Penyusutan</td>
                                    <td class="text-end">(Rp {{ number_format($accumulatedDepreciation, 0, ',', '.') }})</td>
                                </tr>
                                @endif
                                <tr class="fw-bold">
                                    <td>Total Aktiva Tetap</td>
                                    <td class="text-end">Rp {{ number_format($netFixedAssets, 0, ',', '.') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    @endif

                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h6 class="fw-bold mb-0">TOTAL AKTIVA</h6>
                        </div>
                        <div class="card-body">
                            <h5 class="text-end fw-bold mb-0">Rp {{ number_format($totalAssets, 0, ',', '.') }}</h5>
                        </div>
                    </div>
                </div>

                <!-- PASIVA (KANAN) -->
                <div class="col-md-6">
                    <h5 class="text-primary fw-bold mb-3">PASIVA</h5>

                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="fw-bold mb-0">Kewajiban Lancar</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                @if($accountsPayable > 0)
                                <tr>
                                    <td>Hutang Dagang</td>
                                    <td class="text-end">Rp {{ number_format($accountsPayable, 0, ',', '.') }}</td>
                                </tr>
                                @endif
                                @if($taxPayable > 0)
                                <tr>
                                    <td>Hutang Pajak</td>
                                    <td class="text-end">Rp {{ number_format($taxPayable, 0, ',', '.') }}</td>
                                </tr>
                                @endif
                                <tr class="fw-bold">
                                    <td>Total Kewajiban Lancar</td>
                                    <td class="text-end">Rp {{ number_format($totalCurrentLiabilities, 0, ',', '.') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($longTermLiabilities > 0)
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="fw-bold mb-0">Kewajiban Jangka Panjang</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <tr>
                                    <td>Hutang Jangka Panjang</td>
                                    <td class="text-end">Rp {{ number_format($longTermLiabilities, 0, ',', '.') }}</td>
                                </tr>
                                <tr class="fw-bold">
                                    <td>Total Kewajiban Jangka Panjang</td>
                                    <td class="text-end">Rp {{ number_format($longTermLiabilities, 0, ',', '.') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    @endif

                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="fw-bold mb-0">Ekuitas</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <tr>
                                    <td>Modal</td>
                                    <td class="text-end">Rp {{ number_format($initialCapital, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td>Laba Tahun Berjalan</td>
                                    <td class="text-end">Rp {{ number_format($netIncome, 0, ',', '.') }}</td>
                                </tr>
                                <tr class="fw-bold">
                                    <td>Total Ekuitas</td>
                                    <td class="text-end">Rp {{ number_format($totalEquity, 0, ',', '.') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h6 class="fw-bold mb-0">TOTAL PASIVA</h6>
                        </div>
                        <div class="card-body">
                            <h5 class="text-end fw-bold mb-0">Rp {{ number_format($totalLiabilitiesAndEquity, 0, ',', '.') }}</h5>
                        </div>
                    </div>
                </div>
            </div>

            @if(abs($difference) > 0)
                <div class="alert alert-warning mt-4">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Perhatian:</strong> Neraca tidak seimbang. Selisih: Rp {{ number_format(abs($difference), 0, ',', '.') }}
                </div>
            @else
                <div class="alert alert-success mt-4">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>Status:</strong> Neraca seimbang.
                </div>
            @endif

            <div class="mt-4">
                <h6 class="fw-bold">Keterangan:</h6>
                <ul class="small text-muted">
                    <li>Laporan neraca menampilkan posisi keuangan perusahaan pada tanggal {{ \Carbon\Carbon::parse($date)->format('d F Y') }}</li>
                    <li>Saldo kas dan bank diambil dari data saldo awal yang telah diinput atau diestimasi dari transaksi</li>
                    <li>Persediaan dihitung berdasarkan stok saat ini dan harga pokok</li>
                    <li>Laba tahun berjalan dihitung dari pendapatan dikurangi harga pokok penjualan dan beban operasional</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    body * {
        visibility: hidden;
    }
    .card {
        break-inside: avoid;
    }
    .container-fluid, .container-fluid * {
        visibility: visible;
    }
    .container-fluid {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
    .no-print, .no-print * {
        display: none !important;
    }
    a[href]:after {
        content: none !important;
    }
}
</style>
@endsection
