window.demoButtonClickMessage = function (event) {
    event.preventDefault();
    new FilamentNotification().title('Modify this button in your theme folder').icon('heroicon-o-pencil-square').iconColor('info').send();
};

window.KhiCart = {
    csrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    },

    async request(url, method, data = null) {
        const response = await fetch(url, {
            method,
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                'X-CSRF-TOKEN': this.csrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: data ? new URLSearchParams(data) : null,
        });

        const payload = await response.json();

        if (!response.ok) {
            throw new Error(payload.message ?? 'Request failed.');
        }

        return payload;
    },

    formatCurrency(amount) {
        return new Intl.NumberFormat('id-ID').format(amount);
    },

    async updateQuantity(cartItemId, action, itemId) {
        const input = document.getElementById(`counter-input-${itemId}`);

        if (!input) {
            return;
        }

        const quantity = parseInt(input.value, 10);
        const nextQuantity = action === 'plus'
            ? quantity + 1
            : action === 'minus'
                ? quantity - 1
                : parseInt(action, 10);

        if (Number.isNaN(nextQuantity)) {
            return;
        }

        if (nextQuantity < 1) {
            await this.removeItem(cartItemId);
            return;
        }

        try {
            const response = await this.request(`/cart/items/${cartItemId}`, 'PATCH', {
                quantity: nextQuantity,
            });

            if (!response.success) {
                alert(response.message ?? 'Unable to update cart item.');
                return;
            }

            input.value = nextQuantity;

            const itemTotal = document.getElementById(`cart-item-total-${cartItemId}`);
            if (itemTotal) {
                itemTotal.textContent = `Rp${this.formatCurrency(response.data.updatedItemPrice)}`;
            }

            const subtotalValue = this.formatCurrency(response.data.cartSubtotal);
            const cartTotal = document.getElementById('cart-total');
            if (cartTotal) {
                cartTotal.textContent = subtotalValue;
            }
            const cartSubtotalStat = document.getElementById('cart-subtotal-stat');
            if (cartSubtotalStat) {
                cartSubtotalStat.textContent = subtotalValue;
            }

            const count = document.querySelectorAll('.cart-item-card').length;
            const cartItemCountStat = document.getElementById('cart-item-count-stat');
            if (cartItemCountStat) {
                cartItemCountStat.textContent = `${count} items`;
            }
        } catch (error) {
            alert(error.message ?? 'Unable to update cart item.');
        }
    },

    async removeItem(cartItemId) {
        try {
            const response = await this.request(`/cart/items/${cartItemId}`, 'DELETE');

            if (!response.success) {
                alert(response.message ?? 'Unable to remove cart item.');
                return;
            }

            document.getElementById(`cart-item-${cartItemId}`)?.remove();

            const subtotalValue = this.formatCurrency(response.data.cartSubtotal);
            const cartTotal = document.getElementById('cart-total');
            if (cartTotal) {
                cartTotal.textContent = subtotalValue;
            }
            const cartSubtotalStat = document.getElementById('cart-subtotal-stat');
            if (cartSubtotalStat) {
                cartSubtotalStat.textContent = subtotalValue;
            }

            const remainingItems = document.querySelectorAll('.cart-item-card').length;
            const cartItemCountStat = document.getElementById('cart-item-count-stat');
            if (cartItemCountStat) {
                cartItemCountStat.textContent = `${remainingItems} items`;
            }

            if (remainingItems === 0) {
                document.getElementById('cart-items-list')?.classList.add('hidden');
                document.getElementById('cart-empty-state')?.classList.remove('hidden');
            }
        } catch (error) {
            alert(error.message ?? 'Unable to remove cart item.');
        }
    },
};
