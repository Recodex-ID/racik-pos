<div class="h-screen flex flex-col bg-zinc-50 dark:bg-zinc-900">
    <!-- Header POS -->
    <header class="bg-white dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700 px-6 py-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-zinc-900 dark:text-zinc-100">Point of Sale</h1>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $this->currentTenant->name }} - {{ $transactionNumber }}</p>
            </div>
            <div class="flex items-center space-x-4">
                <div class="text-right">
                    <div class="text-sm text-zinc-500 dark:text-zinc-400">Kasir</div>
                    <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ auth()->user()->name }}</div>
                </div>
                <flux:button wire:click="resetTransaction" variant="outline" size="sm" icon="refresh">
                    Transaksi Baru
                </flux:button>
            </div>
        </div>
    </header>

    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-6 py-3 mx-6 mt-4 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 px-6 py-3 mx-6 mt-4 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    <!-- Main Content -->
    <div class="flex-1 flex overflow-hidden">
        <!-- Left Panel - Product Search & Cart -->
        <div class="flex-1 flex flex-col">
            <!-- Product Search -->
            <div class="bg-white dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700 p-6">
                <flux:input
                    wire:model.live.debounce.300ms="productSearch"
                    placeholder="Cari produk (nama/SKU) atau scan barcode..."
                    icon="magnifying-glass"
                    class="text-lg"
                    autofocus
                />
            </div>

            <!-- Product Cards - Scrollable -->
            <div class="flex-1 bg-zinc-50 dark:bg-zinc-900 overflow-y-auto p-6">
                @if($this->products->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                        @foreach($this->products as $product)
                            <div wire:click="addToCart({{ $product->id }})"
                                 class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg p-4 hover:shadow-lg hover:border-blue-300 dark:hover:border-blue-600 cursor-pointer transition-all duration-200 transform hover:scale-105">
                                <div class="flex flex-col h-full">
                                    <div class="flex-1">
                                        <h3 class="font-semibold text-zinc-900 dark:text-zinc-100 mb-1 text-sm leading-tight">{{ $product->name }}</h3>
                                        <p class="text-xs text-zinc-500 dark:text-zinc-400 mb-2">{{ $product->category->name }}</p>
                                        <div class="flex items-center justify-between text-xs text-zinc-600 dark:text-zinc-300 mb-3">
                                            <span>{{ $product->sku }}</span>
                                            <span class="flex items-center bg-zinc-100 dark:bg-zinc-700 px-2 py-1 rounded">
                                                <flux:icon name="cube" class="w-3 h-3 mr-1" />
                                                {{ $product->stock }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="text-center mt-2">
                                        <div class="text-lg font-bold text-blue-600 dark:text-blue-400">
                                            Rp {{ number_format($product->price, 0, ',', '.') }}
                                        </div>
                                        <div class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                                            Klik untuk tambah ke keranjang
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="flex items-center justify-center h-full">
                        <div class="text-center">
                            <div class="w-20 h-20 bg-zinc-200 dark:bg-zinc-700 rounded-lg flex items-center justify-center mx-auto mb-4">
                                <flux:icon name="cube" class="w-10 h-10 text-zinc-400" />
                            </div>
                            <h3 class="font-medium text-zinc-900 dark:text-zinc-100 mb-2">Tidak ada produk</h3>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                @if(strlen($this->productSearch) > 0)
                                    Tidak ditemukan produk yang sesuai dengan pencarian "{{ $this->productSearch }}"
                                @else
                                    Belum ada produk tersedia di toko ini
                                @endif
                            </p>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Shopping Cart -->
            <div class="flex-1 bg-white dark:bg-zinc-800 overflow-hidden flex flex-col">
                <div class="p-6 border-b border-zinc-200 dark:border-zinc-700">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Keranjang Belanja</h2>
                        @if(count($cart) > 0)
                            <flux:button wire:click="clearCart" variant="outline" size="sm" color="red" icon="trash">
                                Kosongkan
                            </flux:button>
                        @endif
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto">
                    @if(count($cart) > 0)
                        <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @foreach($cart as $key => $item)
                                <div class="p-6">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1 mr-4">
                                            <h3 class="font-medium text-zinc-900 dark:text-zinc-100">{{ $item['name'] }}</h3>
                                            <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $item['sku'] }}</p>
                                            <p class="text-sm text-zinc-600 dark:text-zinc-300">
                                                Rp {{ number_format($item['price'], 0, ',', '.') }} x {{ $item['quantity'] }}
                                            </p>
                                        </div>
                                        <div class="text-right">
                                            <div class="font-bold text-lg text-zinc-900 dark:text-zinc-100 mb-2">
                                                Rp {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                <flux:button
                                                    wire:click="updateQuantity('{{ $key }}', {{ $item['quantity'] - 1 }})"
                                                    size="sm" variant="outline" icon="minus" />
                                                <span class="px-3 py-1 bg-zinc-100 dark:bg-zinc-700 rounded text-sm font-medium">
                                                    {{ $item['quantity'] }}
                                                </span>
                                                <flux:button
                                                    wire:click="updateQuantity('{{ $key }}', {{ $item['quantity'] + 1 }})"
                                                    size="sm" variant="outline" icon="plus" />
                                                <flux:button
                                                    wire:click="removeFromCart('{{ $key }}')"
                                                    size="sm" variant="outline" color="red" icon="x-mark" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="flex-1 flex items-center justify-center">
                            <div class="text-center">
                                <div class="w-16 h-16 bg-zinc-100 dark:bg-zinc-700 rounded-lg flex items-center justify-center mx-auto mb-4">
                                    <flux:icon name="shopping-cart" class="w-8 h-8 text-zinc-400" />
                                </div>
                                <h3 class="font-medium text-zinc-900 dark:text-zinc-100 mb-2">Keranjang Kosong</h3>
                                <p class="text-sm text-zinc-500 dark:text-zinc-400">Cari dan tambahkan produk ke keranjang</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Panel - Transaction Summary & Payment -->
        <div class="w-80 bg-white dark:bg-zinc-800 border-l border-zinc-200 dark:border-zinc-700 flex flex-col">
            <!-- Customer Selection -->
            <div class="p-6 border-b border-zinc-200 dark:border-zinc-700">
                <div class="flex items-center justify-between mb-3">
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Pelanggan</label>
                    <flux:button wire:click="$set('showCustomerModal', true)" size="sm" variant="outline" icon="plus">
                        Baru
                    </flux:button>
                </div>
                <flux:select wire:model.live="selectedCustomerId" placeholder="Pilih pelanggan (opsional)">
                    @foreach($this->customers as $customer)
                        <flux:select.option value="{{ $customer->id }}">{{ $customer->name }}</flux:select.option>
                    @endforeach
                </flux:select>
                @if($this->selectedCustomer)
                    <div class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                        {{ $this->selectedCustomer->phone }}
                    </div>
                @endif
            </div>

            <!-- Discount -->
            <div class="p-6 border-b border-zinc-200 dark:border-zinc-700">
                <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-3 block">Diskon</label>
                <div class="grid grid-cols-2 gap-2 mb-3">
                    <flux:button
                        wire:click="$set('discountType', 'percentage')"
                        variant="{{ $discountType === 'percentage' ? 'primary' : 'outline' }}"
                        size="sm">
                        %
                    </flux:button>
                    <flux:button
                        wire:click="$set('discountType', 'amount')"
                        variant="{{ $discountType === 'amount' ? 'primary' : 'outline' }}"
                        size="sm">
                        Rp
                    </flux:button>
                </div>
                <flux:input
                    wire:model.live="discountValue"
                    type="number"
                    step="0.01"
                    placeholder="0" />
            </div>

            <!-- Transaction Summary -->
            <div class="flex-1 p-6">
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Ringkasan</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-zinc-600 dark:text-zinc-400">Subtotal</span>
                        <span class="font-medium">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                    </div>
                    @if($discountAmount > 0)
                        <div class="flex justify-between text-red-600">
                            <span>Diskon</span>
                            <span>-Rp {{ number_format($discountAmount, 0, ',', '.') }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between">
                        <span class="text-zinc-600 dark:text-zinc-400">Pajak ({{ $taxRate }}%)</span>
                        <span class="font-medium">Rp {{ number_format($taxAmount, 0, ',', '.') }}</span>
                    </div>
                    <div class="border-t border-zinc-200 dark:border-zinc-700 pt-3">
                        <div class="flex justify-between">
                            <span class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Total</span>
                            <span class="text-xl font-bold text-zinc-900 dark:text-zinc-100">
                                Rp {{ number_format($totalAmount, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Button -->
            <div class="p-6 border-t border-zinc-200 dark:border-zinc-700">
                <flux:button
                    wire:click="openPaymentModal"
                    variant="primary"
                    class="w-full"
                    :disabled="count($cart) === 0">
                    Bayar
                </flux:button>
            </div>
        </div>
    </div>

    <!-- Customer Modal -->
    <flux:modal wire:model.self="showCustomerModal" name="customer-form" class="min-w-md max-w-lg">
        <form wire:submit.prevent="createCustomer">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">Tambah Pelanggan Baru</flux:heading>
                    <flux:text class="mt-2">Informasi pelanggan untuk transaksi ini</flux:text>
                </div>

                <flux:field>
                    <flux:label>Nama</flux:label>
                    <flux:input wire:model="newCustomerName" placeholder="Nama pelanggan..." />
                    <flux:error name="newCustomerName" />
                </flux:field>

                <flux:field>
                    <flux:label>Telepon</flux:label>
                    <flux:input wire:model="newCustomerPhone" placeholder="Nomor telepon..." />
                    <flux:error name="newCustomerPhone" />
                </flux:field>

                <flux:field>
                    <flux:label>Email</flux:label>
                    <flux:input wire:model="newCustomerEmail" type="email" placeholder="Email..." />
                    <flux:error name="newCustomerEmail" />
                </flux:field>

                <div class="flex gap-2">
                    <flux:spacer />
                    <flux:modal.close>
                        <flux:button variant="ghost">Batal</flux:button>
                    </flux:modal.close>
                    <flux:button type="submit" variant="primary">Simpan</flux:button>
                </div>
            </div>
        </form>
    </flux:modal>

    <!-- Payment Modal -->
    <flux:modal wire:model.self="showPaymentModal" name="payment-form" class="min-w-md max-w-lg">
        <form wire:submit.prevent="processTransaction">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">Pembayaran</flux:heading>
                    <flux:text class="mt-2">Total: Rp {{ number_format($totalAmount, 0, ',', '.') }}</flux:text>
                </div>

                <flux:field>
                    <flux:label>Metode Pembayaran</flux:label>
                    <flux:select wire:model.live="paymentMethod">
                        <flux:select.option value="cash">Tunai</flux:select.option>
                        <flux:select.option value="debit">Kartu Debit</flux:select.option>
                        <flux:select.option value="credit">Kartu Kredit</flux:select.option>
                        <flux:select.option value="transfer">Transfer Bank</flux:select.option>
                        <flux:select.option value="qris">QRIS</flux:select.option>
                    </flux:select>
                </flux:field>

                @if($paymentMethod === 'cash')
                    <div>
                        <flux:label>Jumlah Bayar</flux:label>
                        <flux:input wire:model.live="paymentAmount" type="number" step="0.01" />
                        <flux:error name="paymentAmount" />

                        <div class="grid grid-cols-3 gap-2 mt-3">
                            <flux:button type="button" wire:click="setExactAmount" variant="outline" size="sm">
                                Pas
                            </flux:button>
                            <flux:button type="button" wire:click="addQuickAmount(5000)" variant="outline" size="sm">
                                +5K
                            </flux:button>
                            <flux:button type="button" wire:click="addQuickAmount(10000)" variant="outline" size="sm">
                                +10K
                            </flux:button>
                        </div>

                        @if($changeAmount > 0)
                            <div class="mt-4 p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                                <div class="text-sm text-green-700 dark:text-green-300">Kembalian</div>
                                <div class="text-lg font-bold text-green-800 dark:text-green-200">
                                    Rp {{ number_format($changeAmount, 0, ',', '.') }}
                                </div>
                            </div>
                        @endif
                    </div>
                @else
                    <flux:input wire:model="paymentAmount" type="hidden" :value="$totalAmount" />
                @endif

                <flux:field>
                    <flux:label>Catatan (Opsional)</flux:label>
                    <flux:textarea wire:model="notes" placeholder="Catatan transaksi..." rows="2" />
                </flux:field>

                <div class="flex gap-2">
                    <flux:spacer />
                    <flux:modal.close>
                        <flux:button variant="ghost">Batal</flux:button>
                    </flux:modal.close>
                    <flux:button type="submit" variant="primary">Proses Pembayaran</flux:button>
                </div>
            </div>
        </form>
    </flux:modal>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Listen for barcode scanner input
    let barcodeBuffer = '';
    let barcodeTimeout;

    document.addEventListener('keypress', function(e) {
        // Check if we're not in an input field
        if (e.target.tagName !== 'INPUT' && e.target.tagName !== 'TEXTAREA') {
            clearTimeout(barcodeTimeout);
            barcodeBuffer += e.key;

            // Set timeout to clear buffer (barcode scanners input fast)
            barcodeTimeout = setTimeout(() => {
                if (barcodeBuffer.length > 3) {
                    @this.call('addProductByBarcode', barcodeBuffer);
                }
                barcodeBuffer = '';
            }, 100);
        }
    });
});
</script>
