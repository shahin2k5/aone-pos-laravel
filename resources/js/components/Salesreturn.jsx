import React, { Component } from "react";
import { createRoot } from "react-dom/client";
import axios from "axios";
import Swal from "sweetalert2";
import { isArray, sum } from "lodash";

class Salesreturn extends Component {
    constructor(props) {
        super(props);
        this.state = {
            cart: [],
            products: [],
            customers: [],
            branches: [],
            branchStocks: {}, // New state for branch stocks
            barcode: "",
            search: "",
            customer_id: "",
            customer_info: "",
            translations: {},
            sub_total:0,
            discount_amount:'0',
            gr_total:0,
            prev_balance:0,
            new_balance:0,
            return_amount:'0',
            last_balance:0,
            selCustomerId:'',
            selCustomerFName:'',
            selCustomerLName:'',
            selCustomerAddress:'',
            selCustomerPhone:'',
            selCustomerBalance:'',
            order_id: '',
            printUrl:'',
            isAdmin: window.APP && window.APP.user_role === 'admin' // Check if user is admin
        };

        this.loadCart = this.loadCart.bind(this);
        this.handleOnChangeBarcode = this.handleOnChangeBarcode.bind(this);
        this.handleScanBarcode = this.handleScanBarcode.bind(this);
        this.handleChangeQty = this.handleChangeQty.bind(this);
        this.handleEmptyCart = this.handleEmptyCart.bind(this);

        this.loadProducts = this.loadProducts.bind(this);
        this.loadBranchStocks = this.loadBranchStocks.bind(this); // New method
        this.loadBranches = this.loadBranches.bind(this); // Load branches for admin
        this.handleChangeSearch = this.handleChangeSearch.bind(this);
        this.handleSeach = this.handleSeach.bind(this);
        this.findOrderID = this.findOrderID.bind(this);
        this.handleClickSubmit = this.handleClickSubmit.bind(this);
        this.loadTranslations = this.loadTranslations.bind(this);
    }

    componentDidMount() {
        // load user cart
        this.loadTranslations();
        this.loadProducts();
        this.loadCustomers();
        if (this.state.isAdmin) {
            this.loadBranchStocks(); // Load branch stocks for admin
            this.loadBranches(); // Load branches for admin
        }
        // Load cart after a short delay to ensure other components are loaded
        setTimeout(() => {
            this.loadCart();
        }, 100);
    }

    // load the transaltions for the react component
    loadTranslations() {
        const endpoint = this.state.isAdmin ? "/admin/locale/cart" : "/user/locale/cart";
        axios
            .get(endpoint)
            .then((res) => {
                const translations = res.data;
                this.setState({ translations });
            })
            .catch((error) => {
                console.error("Error loading translations:", error);
            });
    }

    loadCustomers() {
        const endpoint = this.state.isAdmin ? `/admin/customers` : `/user/customers`;
        axios.get(endpoint).then((res) => {
            const customers = res.data;
            this.setState({ customers });
        }).catch((error) => {
            console.error('Error loading customers:', error);
        });
    }

    loadProducts(search = "") {
        const query = !!search ? `?search=${search}` : "";
        const endpoint = this.state.isAdmin ? `/admin/products${query}` : `/user/products${query}`;
        console.log('Loading products from:', endpoint, 'isAdmin:', this.state.isAdmin);
        axios.get(endpoint).then((res) => {
            console.log('Products response:', res.data);
            const products = res.data.data;
            this.setState({ products });
        }).catch((error) => {
            console.error('Error loading products:', error);
        });
    }

    loadBranches() {
        axios.get(`/admin/load-branches`).then((res) => {
            const branches = res.data;
            this.setState({ branches });
        }).catch((error) => {
            console.error('Error loading branches:', error);
        });
    }

    // New method to load branch stocks for admin
    loadBranchStocks() {
        axios.get(`/admin/branch-stocks`).then((res) => {
            const branchStocks = res.data;
            this.setState({ branchStocks });
        }).catch((error) => {
            console.error('Error loading branch stocks:', error);
        });
    }

    handleOnChangeBarcode(event) {
        const barcode = event.target.value;

        this.setState({ barcode });
    }

            loadCart() {
        // Load cart from server
        const endpoint = this.state.isAdmin ? "/admin/salesreturn-cart" : "/user/salesreturn-cart";
        console.log('Loading cart from:', endpoint, 'isAdmin:', this.state.isAdmin);
        axios.get(endpoint).then((res) => {
            console.log('Cart API response:', res.data);
            let cart = res.data;

            // Ensure cart is always an array
            if (!Array.isArray(cart)) {
                console.warn('Cart is not an array, converting to empty array');
                cart = [];
            }

            if(cart && cart.length > 0){
                const sub_total = cart.reduce((total, item) => total + parseFloat(item.total_price || 0), 0).toFixed(2);
                const gr_total = parseFloat(sub_total) - this.state.discount_amount;
                const customer_id = cart[0].customer_id;
                const prev_balance = cart[0].customer?.balance || 0;
                const new_balance = prev_balance - gr_total;
                const last_balance = new_balance;
                this.setState({ cart, sub_total, gr_total, customer_id, prev_balance, new_balance, last_balance, return_amount: this.state.return_amount });
            }else{
                const sub_total = 0;
                const gr_total = 0;
                const customer_id = "";
                const prev_balance = 0;
                const new_balance = 0;
                const last_balance = 0;
                this.setState({ cart: [], sub_total, gr_total, customer_id, prev_balance, new_balance, last_balance, return_amount: this.state.return_amount });
            }
        }).catch((error) => {
            console.error('Error loading cart:', error);
            // Set empty cart on error
            this.setState({
                cart: [],
                sub_total: 0,
                gr_total: 0,
                customer_id: "",
                prev_balance: 0,
                new_balance: 0,
                last_balance: 0,
                return_amount: this.state.return_amount
            });
        });
    }

    handleScanBarcode(event) {
        event.preventDefault();
        const { barcode, customer_id } = this.state;
        if (!!barcode && !!customer_id) {
            axios
                .post("/admin/cart", { barcode, customer_id })
                .then((res) => {
                    this.loadCart();
                    this.setState({ barcode: "" });
                })
                .catch((err) => {
                    Swal.fire("Error!", err.response.data.message, "error");
                });
        }
    }

    handleChangeQty(product_id, qty) {
        var c_product = ""
        const cart = this.state.cart && Array.isArray(this.state.cart) ? this.state.cart.map((c) => {
            if (c.product_id === product_id) {
                c.qnty = parseInt(qty) || 0;
                c.total_price = (parseInt(qty) || 0) * c.sell_price; // Update total_price locally
                c_product = c
            }
            return c;
        }) : [];

        if (!qty) return;

        const endpoint = this.state.isAdmin ? "/admin/salesreturn/changeqnty" : "/user/salesreturn/changeqnty";
        axios
            .post(endpoint, { product_id, qnty: parseInt(qty) || 0, sell_price: c_product.sell_price })
            .then((res) => {
                // Calculate total from updated cart items
                const sub_total = Array.isArray(cart) ? cart.reduce((total, item) => total + parseFloat(item.total_price || 0), 0).toFixed(2) : '0.00';
                const gr_total = parseFloat(sub_total) - this.state.discount_amount;
                const new_balance = this.state.prev_balance + gr_total;
                const last_balance = new_balance - this.state.return_amount;
                this.setState({ cart, sub_total, gr_total, new_balance, last_balance, return_amount: this.state.return_amount });
            })
            .catch((err) => {
                Swal.fire("Error!", err.response.data.message, "error");
            });
    }

    getTotal(cart) {
        if(isArray(cart) && cart && cart.length > 0){
            // Use total_price if available, otherwise calculate from qnty * sell_price
            const total = cart.map((c) => c.total_price || (c.qnty * c.sell_price));
            return sum(total).toFixed(2);
        }
        return '0.00';
    }

    handleClickDelete(product_id) {

        const endpoint = this.state.isAdmin ? "/admin/salesreturn/delete" : "/user/salesreturn/delete";
        axios
            .post(endpoint, { product_id, _method: "POST" })
            .then((res) => {
                const cart = this.state.cart && Array.isArray(this.state.cart) ? this.state.cart.filter((c) => c.product_id !== product_id) : [];
                // Calculate total from remaining cart items
                const sub_total = Array.isArray(cart) ? cart.reduce((total, item) => total + parseFloat(item.total_price || 0), 0).toFixed(2) : '0.00';
                const gr_total = parseFloat(sub_total) - this.state.discount_amount;
                const new_balance = this.state.prev_balance + gr_total;
                const last_balance = new_balance - this.state.discount_amount;

                this.setState({
                    cart,
                    sub_total,
                    gr_total,
                    new_balance,
                    last_balance,
                    return_amount: this.state.return_amount
                });
            });
    }

    handleEmptyCart() {
        const endpoint = this.state.isAdmin ? "/admin/salesreturn-cart/empty" : "/user/salesreturn-cart/empty";
        axios.delete(endpoint).then((res) => {
            if(this.state.customer_id){
                const last_balance = this.state.prev_balance
                this.setState({ cart: [],sub_total:0, gr_total:0, new_balance:last_balance, last_balance, discount_amount:'0', return_amount:'0' });
            }else{
                this.setState({ cart: [],sub_total:0, gr_total:0, new_balance:0, last_balance:0, discount_amount:'0', return_amount:'0' });
            }
        });
    }

    handleChangeSearch(event) {
        const search = event.target.value;
        this.setState({ search });
    }

    handleSeach(event) {
        if (event.keyCode === 13) {
            this.loadProducts(event.target.value);
        }
    }

    addProductToCart(productId) {
        // For sales return, we need to check if we have an order loaded first
        if(!this.state.order_id){
            Swal.fire('Please enter an order ID first', 'warning');
            return false;
        }

        if(!this.state.customer_id){
            Swal.fire('Please load an order first', 'warning');
            return false;
        }

        const customer_id = this.state.customer_id;
        let product = this.state.products.find((p) => p.id === productId);

        if (!product) {
            Swal.fire('Product not found', 'error');
            return false;
        }

        // Visual feedback
        const elements = document.querySelectorAll('[class*="product-"]');
        elements.forEach(el => {
            el.style.border = '0px';
        });

        const prodBarcode = document.getElementById('product-'+product.barcode);
        if(prodBarcode){
           prodBarcode.style.border = "2px solid #fcc";
        }

                // Add product to sales return cart via API
        const endpoint = this.state.isAdmin ? "/admin/salesreturn-cart" : "/user/salesreturn-cart";
        axios
            .post(endpoint, {
                product_id: product.id,
                barcode: product.barcode,
                customer_id: customer_id
            })
            .then((res) => {
                // Reload cart to get updated data from server
                this.loadCart();
            })
            .catch((err) => {
                Swal.fire("Error!", err.response?.data?.message || "Failed to add product to cart", "error");
            });
    }

    findOrderID(event) {
        const order_id = event.target.value
        const key_code = event.keyCode

        if(order_id && key_code == 13){

            const endpoint = this.state.isAdmin ? `/admin/salesreturn/findorderid/${order_id}` : `/user/salesreturn/findorderid/${order_id}`;
            axios.get(endpoint).then((res) => {
                const order = res.data.order;
                const salesreturn_items = res.data.salesreturn_item;

                console.log('=== SALE DETAILS FETCHED ===');
                console.log('Order:', order);
                console.log('Order ID:', order?.id);
                console.log('Order Total:', order?.gr_total);
                console.log('Order Sub Total:', order?.sub_total);
                console.log('Order Items:', order?.items);
                console.log('Sales Return Items:', salesreturn_items);
                console.log('Cart Items Details:');
                salesreturn_items.forEach((item, index) => {
                    console.log(`Item ${index + 1}:`, {
                        product_id: item.product_id,
                        product_name: item.product?.name,
                        quantity: item.qnty,
                        sell_price: item.sell_price,
                        total_price: item.total_price,
                        purchase_price: item.purchase_price
                    });
                });
                console.log('=== END SALE DETAILS ===');

                if(order){

                    // Use backend-calculated totals instead of recalculating
                    const sub_total = parseFloat(order.gr_total || 0).toFixed(2);
                    const gr_total = parseFloat(sub_total) - this.state.discount_amount;
                    const customer_id = order.customer_id;
                    const user_balance = order.customer.balance;
                    const prev_balance = user_balance;
                    const new_balance = user_balance - gr_total;
                    const last_balance = new_balance;
                    this.setState({
                        order_id,
                        sub_total,
                        gr_total,
                        customer_id,
                        prev_balance,
                        new_balance,
                        last_balance,
                        customer_info: order.customer,
                        cart: salesreturn_items,
                        selCustomerFName: order.customer.first_name,
                        selCustomerLName: order.customer.last_name,
                        selCustomerAddress: order.customer.address,
                        selCustomerPhone: order.customer.phone,
                        selCustomerBalance: order.customer.balance,
                        return_amount: this.state.return_amount,
                    });
                }else{
                    const order_id = ""
                    const sub_total = 0
                    const gr_total = 0
                    const customer_id = ""
                    const prev_balance = 0
                    const new_balance = 0
                    const last_balance = 0
                    this.setState({ order_id,  cart:[], sub_total, gr_total,customer_id, prev_balance, new_balance,last_balance, return_amount: this.state.return_amount });
                }



        });

        }


    }

    printInvoice = () => {
        const invoiceUrl = `/admin/salesreturn/print/${this.state.saleId}`;
        window.open(invoiceUrl, "_blank");
    };

    handleClickSubmit() {
        if(!this.state.customer_id){
            Swal.fire('Please select a customer', 'warning');
            return false
        }
        Swal.fire({
            title: 'Save Sales Return',
            input: "text",
            inputValue: this.state.return_amount,
            showCancelButton: true,
            confirmButtonText: "Save Sale Return",
            cancelButtonText: this.state.translations["cancel_pay"] || "Cancel",
            showLoaderOnConfirm: true,
            preConfirm: (amount) => {
                const endpoint = this.state.isAdmin ? "/admin/salesreturn/finalsave" : "/user/salesreturn/finalsave";
                return axios
                    .post(endpoint, {
                        customer_id: this.state.customer_id,
                        order_id: this.state.order_id,
                        amount,
                    })
                                        .then((res) => {
                        this.loadCart();
                        // Set the print URL for printing
                        const printUrl = this.state.isAdmin ? `/admin/salesreturn/print/${res.data.id}` : `/user/salesreturn/print/${res.data.id}`;
                        this.setState({
                            printUrl,
                            return_amount:'0'
                        });
                        Swal.fire("Success", "Sales return has been saved!", "success");
                        return res.data;
                    })
                    .catch((err) => {
                        Swal.showValidationMessage(err.response.data.message);
                    });
            },
            allowOutsideClick: () => !Swal.isLoading(),
        }).then((result) => {
            if (result.value) {
                //
            }
        });
    }

    changeSubTotal = () =>{
        this.setState({
            sub_total:''
        })
    }

    changeDiscount = (e) =>{
        const discount_amount = e.target.value
        const gr_total = this.state.sub_total - discount_amount
        this.setState({
            discount_amount,
            gr_total
        })
    }

    changeReturnAmount = (e) =>{

        const new_balance = this.state.prev_balance
        const return_amount = e.target.value
        const last_balance = parseFloat(new_balance) + parseFloat(return_amount)

        this.setState({
            return_amount,
            new_balance,
            last_balance
        })

    }

    numberFormat = (amount) =>{
        if(amount>0) {
            return Number(amount).toFixed(2)
        }else{
            return 0.00
        }
    }

    render() {
        try {
            const { cart, products, customers, barcode, translations } = this.state;
            const cartList = Array.isArray(cart) ? cart : [];
            const productList = Array.isArray(products) ? products : [];
            const customerList = Array.isArray(customers) ? customers : [];
            const safeTranslations = translations || {};

        return (
            <div className="row">

                <div className="col-md-6 col-lg-6">
                    <div className="row mb-2">
                        <div className="col-12 mb-2">
                            <div className="alert alert-info p-2" style={{fontSize: '0.95em'}}>
                                <strong>Tip:</strong> Enter the <b>original order ID</b> in the field below and press <b>Enter</b> to load the order for sales return.
                            </div>
                        </div>
                        <div className="col-md-4">
                            <input
                                type="text"
                                onChange={(e) => this.setState({ order_id: e.target.value })}
                                onKeyUp={this.findOrderID}
                                value={this.state.order_id || ''}
                                name="order-input"
                                id="order-input"
                                className="form-control border"
                                placeholder="Enter order ID and press Enter..."
                            />
                        </div>
                        <div className="col-md-8">
                            <div className="row">
                                <div className="col-md-3"><span className="text-danger"><b>{this.state.selCustomerFName || ''} {this.state.selCustomerLName || ''}</b></span></div>
                                <div className="col-md-4"><span className="text-danger"><b style={{wordBreak: 'break-word', overflowWrap: 'break-word'}}>{this.state.selCustomerAddress || ''}</b></span></div>
                                <div className="col-md-3"><span className="text-danger"><b style={{whiteSpace: 'nowrap'}}>{this.state.selCustomerPhone || ''}</b></span></div>
                                <div className="col-md-2"><span className="text-danger"><b>{this.state.selCustomerBalance || ''} BDT</b></span></div>
                            </div>
                        </div>
                    </div>

                    <div className="user-cart mt-1">
                        <div className="card">
                            <table className="table table-striped">
                                <thead>
                                    <tr>
                                        <th>{safeTranslations["product_name"] || "Product Name"}</th>
                                        <th>{safeTranslations["quantity"] || "Quantity"}</th>
                                        <th className="text-right">Total Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {cartList.map((c) => (
                                        <tr key={c.id}>
                                            <td>{c.product?c.product.name:''}</td>
                                            <td>
                                                <input
                                                    type="text"
                                                    className="form-control form-control-sm qty product-input"
                                                    value={parseInt(c.qnty) || 0}
                                                    onChange={(event) =>
                                                        this.handleChangeQty(
                                                            c.id,
                                                            event.target.value
                                                        )
                                                    }

                                                    id={'prodinput-'+c.id}
                                                />
                                                <button
                                                    className="btn btn-danger btn-sm"
                                                    onClick={() =>
                                                        this.handleClickDelete(
                                                            c.product_id
                                                        )
                                                    }
                                                >
                                                    <i className="fas fa-trash"></i>
                                                </button>
                                            </td>
                                            <td className="text-right">
                                                {window.APP.currency_symbol}{" "}
                                                {(parseFloat(c.sell_price) * (parseInt(c.qnty) || 0)).toFixed(2)}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div className="row">
                        <div className="col">
                            <div className="font-weight-bold">Sub. Total</div>

                            <div>{window.APP.currency_symbol} {this.numberFormat(this.state.gr_total)}</div>
                        </div>
                        <div className="col">
                            {/* <div className="font-weight-bold">Discount (0.00)</div>
                            <input type="text" readOnly={true} onChange={this.changeDiscount} placeholder="Discount amount" name="discount" id="discount" className="form-control form-sm text-right"/> */}

                            <div className="font-weight-bold">Return Amount</div>
                            <input type="text"  onChange={this.changeReturnAmount}  value={this.state.return_amount} name="discount" id="discount" placeholder="Paid amount" className="form-control form-sm text-right"/>
                        </div>
                        <div className="col text-right">

                            <div className="font-weight-bold">Last Balance </div>
                            <input type="text" value={this.numberFormat(this.state.last_balance)} readOnly name="discount" id="discount" className="form-control form-sm text-right"/>
                        </div>
                    </div>
                     <div className="row">
                        <div className="col">
                            {/* <div className="font-weight-bold">Total Balance</div>
                            <input type="text" value={this.numberFormat(this.state.new_balance)} readOnly name="discount" id="discount" className="form-control form-sm text-right"/> */}
                        </div>
                        <div className="col">

                        </div>
                        <div className="col text-right">

                        </div>
                    </div>
                    <div className="row mt-3">
                        <div className="col">
                            {this.state.printUrl ? (
                                <a
                                    href={this.state.printUrl}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="btn btn-success btn-block"
                                >
                                    🖨️ Print Sales Return
                                </a>
                            ) : (
                                <a
                                    href="#"
                                    rel="noopener noreferrer"
                                    className="btn btn-success btn-block"
                                    style={{ opacity: 0.5, pointerEvents: 'none' }}
                                >
                                    🖨️ Print Sales Return
                                </a>
                            )}
                        </div>
                        <div className="col">
                            <button
                                type="button"
                                className="btn btn-danger btn-block"
                                onClick={this.handleEmptyCart}
                                disabled={!cart.length}
                            >
                                {safeTranslations["cancel"] || "Cancel"}
                            </button>
                        </div>
                        <div className="col">
                            <button
                                type="button"
                                className="btn btn-primary btn-block"
                                disabled={!cart.length}
                                onClick={this.handleClickSubmit}
                            >
                                {"Confirm Salesreturn"}
                            </button>
                        </div>
                    </div>
                </div>


                <div className="col-md-6 col-lg-6">
                    <div className="row">
                        <div className="col">
                            <form onSubmit={this.handleScanBarcode}>
                                <input
                                    type="text"
                                    className="form-control"
                                    placeholder={safeTranslations["scan_barcode"] || "Scan Barcode..."}
                                    value={barcode || ''}
                                    onChange={this.handleOnChangeBarcode}
                                />
                            </form>
                        </div>

                        <div className="col-lg-8 mb-2">
                            <input
                                type="text"
                                className="form-control"
                                placeholder={(safeTranslations["search_product"] || "Search by Product Name") + "..."}
                                value={this.state.search || ''}
                                onChange={this.handleChangeSearch}
                                onKeyDown={this.handleSeach}
                            />
                        </div>

                    </div>

                    <div className="mb-3">
                        <h5 style={{ marginBottom: '10px', color: '#333', fontWeight: 'bold' }}>
                            Available Products ({productList.length})
                        </h5>
                    </div>

                    <div className="order-product">
                        {productList.map((p) => {
                            // Get branch stocks for this product if admin
                            const productBranchStocks = this.state.isAdmin && this.state.branchStocks[p.id] ? this.state.branchStocks[p.id] : [];
                            const isInCart = this.state.cart && Array.isArray(this.state.cart) ? this.state.cart.some(item => item.product_id === p.id) : false;

                            return (
                                <div
                                    onClick={() => this.addProductToCart(p.id)}
                                    key={p.id}
                                    className="item product-div"
                                    id={'product-'+p.barcode}
                                    style={{
                                        width: this.state.isAdmin ? '150px' : '100px',
                                        overflow:'hidden',
                                        height: this.state.isAdmin ? '180px' : '140px',
                                        border: isInCart ? '3px solid #28a745' : '1px solid #ddd',
                                        borderRadius: '8px',
                                        padding: '8px',
                                        margin: '5px',
                                        cursor: 'pointer',
                                        backgroundColor: isInCart ? '#f8fff9' : '#fff',
                                        position: 'relative'
                                    }}
                                    title={p.name}
                                >
                                    {/* Selected indicator */}
                                    {isInCart && (
                                        <div style={{
                                            position: 'absolute',
                                            top: '5px',
                                            right: '5px',
                                            backgroundColor: '#28a745',
                                            color: 'white',
                                            borderRadius: '50%',
                                            width: '20px',
                                            height: '20px',
                                            display: 'flex',
                                            alignItems: 'center',
                                            justifyContent: 'center',
                                            fontSize: '12px',
                                            fontWeight: 'bold',
                                            zIndex: 1
                                        }}>
                                            ✓
                                        </div>
                                    )}
                                                                        <img src={p.image_url} alt="" style={{ width: '100%', height: '60px', objectFit: 'cover' }} />
                                    <h6
                                        style={{
                                            fontSize: '14px',
                                            margin: '5px 0',
                                            color: 'black',
                                            fontWeight: 'bold',
                                            lineHeight: '1.2'
                                        }}
                                    >
                                        {p.name}
                                    </h6>

                                    {this.state.isAdmin && productBranchStocks.length > 0 ? (
                                        <div style={{ fontSize: '12px', color: '#666' }}>
                                            <div style={{ fontWeight: 'bold', marginBottom: '4px', fontSize: '13px' }}>Branch Stock:</div>
                                            {productBranchStocks.map((stock, index) => {
                                                // Determine color: red for low stock, green for good stock
                                                let textColor = 'green'; // Default: green for sufficient stock
                                                let fontWeight = 'normal';

                                                const warningQty = 20; // Warning threshold set to 20
                                                const stockQty = parseInt(stock.quantity);
                                                const isNegativeStock = stockQty < 0;
                                                const isOutOfStock = stockQty === 0;
                                                const isLowStock = stockQty > 0 && stockQty < warningQty;

                                                if (isNegativeStock) {
                                                    textColor = 'red'; // Red for negative stock
                                                    fontWeight = 'bold';
                                                } else if (isOutOfStock) {
                                                    textColor = 'red'; // Red for out of stock (0)
                                                } else if (isLowStock) {
                                                    textColor = '#ff8c00'; // Orange/warning for low stock (1-19)
                                                }

                                                return (
                                                    <div key={index} style={{
                                                        display: 'flex',
                                                        justifyContent: 'space-between',
                                                        marginBottom: '3px',
                                                        color: textColor,
                                                        fontSize: '12px',
                                                        fontWeight: fontWeight
                                                    }}>
                                                        <span>{stock.branch_name}:</span>
                                                        <span style={{ fontWeight: 'bold' }}>{parseInt(stock.quantity) || 0}</span>
                                                    </div>
                                                );
                                            })}
                                        </div>
                                    ) : (
                                        <div style={{
                                            fontSize: '14px',
                                            color: (() => {
                                                const stockQty = parseInt(p.quantity);
                                                if (stockQty < 0) return 'red'; // Red for negative stock
                                                if (stockQty === 0) return 'red'; // Red for out of stock
                                                if (stockQty < 20) return '#ff8c00'; // Orange for low stock (under 20)
                                                return 'green'; // Green for sufficient stock (20+)
                                            })(),
                                            fontWeight: 'bold'
                                        }}>
                                            Stock: {parseInt(p.quantity) || 0}
                                        </div>
                                    )}
                                </div>
                            );
                        })}
                    </div>
                </div>



            </div>
        );
        } catch (error) {
            console.error('Error in render:', error);
            return (
                <div className="row">
                    <div className="col-12">
                        <div className="alert alert-danger">
                            <h4>Error Loading Sales Return Page</h4>
                            <p>There was an error loading the sales return page. Please refresh the page and try again.</p>
                            <p>Error details: {error.message}</p>
                        </div>
                    </div>
                </div>
            );
        }
    }
}

export default Salesreturn;

const root = document.getElementById("salesreturn-cart");
if (root) {
    const rootInstance = createRoot(root);
    rootInstance.render(<Salesreturn />);
}
