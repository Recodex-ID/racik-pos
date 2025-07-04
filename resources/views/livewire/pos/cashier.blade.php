@php
    use Illuminate\Support\Facades\Storage;
@endphp

<div class="h-screen flex flex-col bg-zinc-50 dark:bg-zinc-900">
    <!-- Top Header -->
    <header class="bg-white dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700 px-6 py-4 flex-shrink-0">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                @role('Admin')
                <a href="{{ route('dashboard') }}" wire:navigate>
                    <flux:button type="submit" variant="outline" color="gray" size="sm" icon="arrow-left">
                        Dashboard
                    </flux:button>
                </a>
                @else
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <flux:button type="submit" variant="primary" color="red" size="sm" icon="arrow-right-start-on-rectangle">
                        Logout
                    </flux:button>
                </form>
                @endrole
                <div>
                    <h1 class="text-xl font-bold text-zinc-900 dark:text-zinc-100">Point of Sale</h1>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">
                        {{ $this->currentTenant->name }} - {{ $transactionNumber }}
                        @if($currentDraftId)
                            <span class="inline-flex items-center px-2 py-1 ml-2 text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 rounded-full">
                                <flux:icon name="document-text" class="w-3 h-3 mr-1" />
                                Draft
                            </span>
                        @endif
                    </p>
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <div class="text-right">
                    <div class="text-sm text-zinc-500 dark:text-zinc-400">Kasir</div>
                    <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ auth()->user()->name }}</div>
                </div>
                <div class="flex items-center space-x-2">
                    <flux:button wire:click="$set('showDraftsModal', true)" variant="primary" color="yellow" size="sm" icon="document-text">
                        Draft ({{ $this->drafts->count() }})
                    </flux:button>
                    <flux:button wire:click="resetTransaction" variant="primary" color="green" size="sm" icon="refresh">
                        Transaksi Baru
                    </flux:button>
                </div>
            </div>
        </div>
    </header>

    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="px-6 py-2">
            <flux:callout variant="success" icon="check-circle" heading="{{ session('success') }}" />
        </div>
    @endif

    @if (session()->has('error'))
        <div class="px-6 py-2">
            <flux:callout variant="danger" icon="x-circle" heading="{{ session('error') }}" />
        </div>
    @endif

    <!-- Main Content: 2 Column Layout -->
    <div class="flex-1 flex overflow-hidden">
        <!-- Left Column: Products -->
        <div class="flex-1 flex flex-col">
            <!-- Product Search -->
            <div class="bg-white dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700 p-4">
                <flux:input
                    wire:model.live.debounce.300ms="productSearch"
                    placeholder="Cari produk (nama/deskripsi)..."
                    icon="magnifying-glass"
                    class="text-lg"
                    autofocus
                />
            </div>

            <!-- Category Tabs -->
            <div class="bg-white dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700 p-4">
                <div class="flex gap-2 overflow-x-auto">
                    <flux:button
                        wire:click="selectCategory(null)"
                        variant="{{ $selectedCategoryId === null ? 'primary' : 'outline' }}"
                        size="sm">
                        Semua
                    </flux:button>
                    @foreach($this->categories as $category)
                        <flux:button
                            wire:click="selectCategory({{ $category->id }})"
                            variant="{{ $selectedCategoryId == $category->id ? 'primary' : 'outline' }}"
                            size="sm">
                            {{ $category->name }}
                        </flux:button>
                    @endforeach
                </div>
            </div>

            <!-- Product Grid -->
            <div class="flex-1 overflow-y-auto p-4 bg-zinc-50 dark:bg-zinc-900">
                @if($this->products->count() > 0)
                    <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                        @foreach($this->products as $product)
                            <div wire:click="addToCart({{ $product->id }})"
                                 class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg p-4 hover:shadow-lg hover:border-blue-300 dark:hover:border-blue-600 cursor-pointer transition-all duration-200 transform hover:scale-105">
                                <div class="flex flex-col h-full">
                                    <!-- Product Image -->
                                    <div class="mb-3">
                                        @if($product->image)
                                            <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}" class="w-full h-24 object-cover rounded-lg">
                                        @else
                                            <div class="w-full h-24 bg-zinc-100 dark:bg-zinc-700 rounded-lg flex items-center justify-center">
                                                <span class="text-2xl font-bold text-zinc-400">{{ $product->getInitials() }}</span>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="flex-1">
                                        <h3 class="font-semibold text-zinc-900 dark:text-zinc-100 mb-1 text-sm leading-tight">{{ $product->name }}</h3>
                                        <p class="text-xs text-zinc-500 dark:text-zinc-400 mb-2">{{ $product->category->name }}</p>
                                    </div>
                                    <div class="text-center mt-2">
                                        <div class="text-sm font-bold text-blue-600 dark:text-blue-400">
                                            Rp {{ number_format($product->price, 0, ',', '.') }}
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
        </div>

        <!-- Right Column: Cart & Checkout -->
        <div class="w-96 bg-white dark:bg-zinc-800 border-l border-zinc-200 dark:border-zinc-700 flex flex-col">
            <!-- Cart Header -->
            <div class="p-4 border-b border-zinc-200 dark:border-zinc-700">
                <div class="flex items-center justify-between mb-4">
                    <flux:heading size="lg">Keranjang Belanja</flux:heading>
                    @if(count($cart) > 0)
                        <flux:button wire:click="clearCart" variant="primary" size="sm" color="red" icon="trash">
                            Kosongkan
                        </flux:button>
                    @endif
                </div>

                <!-- Customer Selection -->
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <flux:label class="text-sm">Pelanggan</flux:label>
                        <flux:button wire:click="$set('showCustomerModal', true)" size="xs" variant="primary" color="blue" icon="plus">
                            Baru
                        </flux:button>
                    </div>
                    <flux:select wire:model.live="selectedCustomerId" placeholder="Pilih pelanggan (opsional)" size="sm">
                        @foreach($this->customers as $customer)
                            <flux:select.option value="{{ $customer->id }}">{{ $customer->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    @if($this->selectedCustomer)
                        <div class="text-xs text-zinc-600 dark:text-zinc-400">
                            {{ $this->selectedCustomer->phone }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Cart Items -->
            <div class="flex-1 overflow-y-auto p-4">
                @if(count($cart) > 0)
                    <div class="space-y-3">
                        @foreach($cart as $key => $item)
                            <div class="p-3 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                                <div class="flex items-start justify-between mb-2">
                                    <div class="flex items-start space-x-3">
                                        @if($item['image'])
                                            <img src="{{ Storage::url($item['image']) }}" alt="{{ $item['name'] }}" class="w-12 h-12 object-cover rounded-md">
                                        @else
                                            <div class="w-12 h-12 bg-zinc-100 dark:bg-zinc-600 rounded-md flex items-center justify-center">
                                                <span class="text-lg font-bold text-zinc-400">{{ $item['initials'] }}</span>
                                            </div>
                                        @endif
                                        <div class="flex-1">
                                            <h3 class="font-medium text-zinc-900 dark:text-zinc-100 text-sm">{{ $item['name'] }}</h3>
                                            <p class="text-xs text-zinc-600 dark:text-zinc-300">
                                                Rp {{ number_format($item['price'], 0, ',', '.') }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-bold text-sm text-zinc-900 dark:text-zinc-100">
                                            Rp {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between pl-16">
                                    <div class="flex items-center space-x-1">
                                        <flux:button
                                            wire:click="updateQuantity('{{ $key }}', {{ $item['quantity'] - 1 }})"
                                            size="xs" variant="primary" color="yellow" icon="minus" />
                                        <span class="px-2 py-1 bg-white dark:bg-zinc-600 rounded text-xs font-medium min-w-[2rem] text-center">
                                            {{ $item['quantity'] }}
                                        </span>
                                        <flux:button
                                            wire:click="updateQuantity('{{ $key }}', {{ $item['quantity'] + 1 }})"
                                            size="xs" variant="primary" color="green" icon="plus" />
                                    </div>
                                    <flux:button
                                        wire:click="removeFromCart('{{ $key }}')"
                                        size="xs" variant="primary" color="red" icon="x-mark" />
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <div class="w-16 h-16 bg-zinc-100 dark:bg-zinc-700 rounded-lg flex items-center justify-center mx-auto mb-4">
                            <flux:icon name="shopping-cart" class="w-8 h-8 text-zinc-400" />
                        </div>
                        <h3 class="font-medium text-zinc-900 dark:text-zinc-100 mb-2">Keranjang Kosong</h3>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">Pilih produk untuk ditambahkan ke keranjang</p>
                    </div>
                @endif
            </div>

            <!-- Discount & Total -->
            <div class="p-4 border-t border-zinc-200 dark:border-zinc-700 space-y-4">
                <!-- Discount -->
                <div class="space-y-2">
                    <flux:label class="text-sm">Diskon</flux:label>
                    <div class="grid grid-cols-2 gap-2">
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
                        placeholder="0"
                        size="sm" />
                </div>

                <!-- Transaction Summary -->
                <div class="space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-zinc-600 dark:text-zinc-400">Subtotal</span>
                        <span class="font-medium">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                    </div>
                    @if($discountAmount > 0)
                        <div class="flex justify-between text-sm text-red-600">
                            <span>Diskon</span>
                            <span>-Rp {{ number_format($discountAmount, 0, ',', '.') }}</span>
                        </div>
                    @endif
                    <div class="border-t border-zinc-200 dark:border-zinc-700 pt-2">
                        <div class="flex justify-between">
                            <span class="font-semibold text-zinc-900 dark:text-zinc-100">Total</span>
                            <span class="text-lg font-bold text-zinc-900 dark:text-zinc-100">
                                Rp {{ number_format($totalAmount, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="space-y-2">
                    <flux:button
                        wire:click="saveToDraft"
                        variant="primary"
                        color="yellow"
                        size="sm"
                        icon="document-plus"
                        class="w-full"
                        :disabled="count($cart) === 0">
                        {{ $currentDraftId ? 'Update Draft' : 'Simpan Draft' }}
                    </flux:button>
                    <flux:button
                        wire:click="openPaymentModal"
                        variant="primary"
                        size="sm"
                        class="w-full"
                        :disabled="count($cart) === 0">
                        Bayar Sekarang
                    </flux:button>
                </div>
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

    <!-- Drafts Modal -->
    <flux:modal wire:model.self="showDraftsModal" name="drafts" variant="flyout" position="right">
        <div class="space-y-6">
            <div class="flex items-center justify-between pt-8">
                <flux:heading size="lg">Draft Transaksi</flux:heading>
            </div>

            <flux:separator/>

            <div class="flex-1 overflow-y-auto max-h-96">
                @if($this->drafts->count() > 0)
                    <div class="space-y-4">
                        @foreach($this->drafts as $draft)
                            <div class="p-4 bg-zinc-50 dark:bg-zinc-800 rounded-lg">
                                <div class="flex items-start justify-between mb-3">
                                    <div class="flex-1">
                                        <h3 class="font-medium text-zinc-900 dark:text-zinc-100">
                                            {{ $draft->transaction_number }}
                                        </h3>
                                        <p class="text-sm text-zinc-600 dark:text-zinc-300">
                                            {{ $draft->updated_at->diffForHumans() }}
                                        </p>
                                        @if($draft->customer)
                                            <p class="text-sm text-zinc-600 dark:text-zinc-300">
                                                {{ $draft->customer->name }}
                                            </p>
                                        @endif
                                    </div>
                                    <div class="text-right">
                                        <div class="font-bold text-lg text-zinc-900 dark:text-zinc-100">
                                            Rp {{ number_format($draft->total_amount, 0, ',', '.') }}
                                        </div>
                                        <div class="text-sm text-zinc-500 dark:text-zinc-400">
                                            {{ $draft->transactionItems->count() }} item
                                        </div>
                                    </div>
                                </div>

                                <!-- Draft Items Preview -->
                                <div class="mb-3">
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400 space-y-1">
                                        @foreach($draft->transactionItems->take(3) as $item)
                                            <div>{{ $item->product->name }} x{{ $item->quantity }}</div>
                                        @endforeach
                                        @if($draft->transactionItems->count() > 3)
                                            <div>... dan {{ $draft->transactionItems->count() - 3 }} item lainnya</div>
                                        @endif
                                    </div>
                                </div>

                                <div class="flex gap-2">
                                    <flux:button
                                        wire:click="loadPendingTransaction({{ $draft->id }})"
                                        variant="primary"
                                        size="sm"
                                        class="flex-1">
                                        Muat Draft
                                    </flux:button>
                                    <flux:button
                                        wire:click="deleteDraft({{ $draft->id }})"
                                        variant="primary"
                                        color="red"
                                        size="sm"
                                        icon="trash"
                                        wire:confirm="Yakin ingin menghapus draft ini?">
                                        Hapus
                                    </flux:button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <div class="w-16 h-16 bg-zinc-100 dark:bg-zinc-700 rounded-lg flex items-center justify-center mx-auto mb-4">
                            <flux:icon name="document-text" class="w-8 h-8 text-zinc-400" />
                        </div>
                        <h3 class="font-medium text-zinc-900 dark:text-zinc-100 mb-2">Belum Ada Draft</h3>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">
                            Draft transaksi akan muncul di sini ketika Anda menyimpannya
                        </p>
                    </div>
                @endif
            </div>

            <div class="flex gap-2">
                <flux:modal.close>
                    <flux:button variant="outline" class="flex-1">Tutup</flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>
</div>
