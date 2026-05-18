# Customer Function Test List

## Checkout First
- [x] Cart page opens for customers and shows medicine, vendor, unit price, quantity, and total.
- [x] Checkout page shows invoice lines and total.
- [x] Checkout page has a shipping address field prefilled from the customer profile.
- [x] Checkout page has payment method options.
- [x] Checkout page has a confirm order action.
- [x] Confirm order creates order, order items, and payment record.
- [x] Confirm order clears the cart after success.
- [x] Confirm order prevents insufficient stock.
- [x] Customer order list opens after checkout and shows order status.
- [x] Admin request page shows the new pending order.
- [x] Admin rejecting a pending order restores reserved stock.

## Remaining Customer Flow
- [x] Browse page lists medicines with vendor, category, type, price, stock, and description.
- [x] Category/type/vendor filters keep selected state.
- [x] Live search renders updated medicine cards.
- [x] Live search keeps add-to-cart controls for customers.
- [x] Add to cart validates medicine and requested quantity.
- [x] Add to cart prevents cart quantity exceeding available stock.
- [x] Cart quantity update validates against stock.
- [x] Remove from cart deletes the selected medicine.
- [x] Run browser/database functional pass on local XAMPP data.

## Reported Fixes
- [x] Login page no longer shows demo password text.
- [x] Cart navigation shows a numeric count badge.
- [x] Customer orders include an invoice PDF download.
- [x] Registration/profile mobile number must be 11 digits and start with 013, 014, 015, 016, 017, 018, or 019.
- [x] Admin accept/reject updates only the selected pending order.
- [x] Medicine cards render stored medicine images, with demo image files present.
