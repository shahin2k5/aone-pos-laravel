import React, { Component } from "react";
import { createRoot } from "react-dom/client";
import axios from "axios";
import Swal from "sweetalert2";
import { isArray, sum } from "lodash";

class Cart extends Component {
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
            branch_id: "",
            selectedBranchId: "", // New state for selected branch in POS
            translations: {},
            sub_total:0,
            discount_amount:0,
            gr_total:0,
            prev_balance:0,
            new_balance:0,
            paid_amount:'',
            last_balance:0,
            selCustomerId:'',
            selCustomerFName:'',
            selCustomerLName:'',
            selCustomerAddress:'',
            selCustomerPhone:'',
            selCustomerBalance:'',
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
        this.setCustomerId = this.setCustomerId.bind(this);
        this.setBranchId = this.setBranchId.bind(this);
        this.setSelectedBranchId = this.setSelectedBranchId.bind(this); // New method for POS branch selection
        this.canAddToCart = this.canAddToCart.bind(this); // Helper method to check if can add to cart
        this.validateCartForNewBranch = this.validateCartForNewBranch.bind(this); // Validate cart for new branch
        this.isProductInCart = this.isProductInCart.bind(this); // Helper method to check if product is in cart
        this.handleClickSubmit = this.handleClickSubmit.bind(this);
        this.loadTranslations = this.loadTranslations.bind(this);
    }

    // Helper to get unique cache key per user and role
    getCacheKey() {
        const userRole = window.APP && window.APP.user_role ? window.APP.user_role : 'user';
        const userId = window.APP && window.APP.user_id ? window.APP.user_id : 'unknown';
        return `pos_cart_data_${userRole}_${userId}`;
    }

    componentDidMount() {
        // load user cart
        this.loadTranslations();

        // Load saved data from localStorage
        const savedData = this.loadSavedData();

        // Set branch_id from window.APP
        this.setState({
            branch_id: window.APP.branch_id,
            ...savedData
        }, () => {
            this.loadCustomers();
            this.loadProducts();
            this.loadCart();
            if (this.state.isAdmin) {
                this.loadBranchStocks(); // Load branch stocks for admin
                this.loadBranches(); // Load branches for admin
            }
        });
    }

    loadSavedData() {
        try {
            const cacheKey = this.getCacheKey();
            const savedData = localStorage.getItem(cacheKey);
            return savedData ? JSON.parse(savedData) : {};
        } catch (error) {
            console.error('Error loading saved data:', error);
            return {};
        }
    }

    saveData() {
        try {
            const cacheKey = this.getCacheKey();
            const dataToSave = {
                customer_id: this.state.customer_id,
                selectedBranchId: this.state.selectedBranchId,
                discount_amount: this.state.discount_amount,
                paid_amount: this.state.paid_amount
            };
            localStorage.setItem(cacheKey, JSON.stringify(dataToSave));
        } catch (error) {
            console.error('Error saving data:', error);
        }
    }

    // load the transaltions for the react component
    loadTranslations() {
        axios
            .get("/user/locale/cart")
            .then((res) => {
                const translations = res.data;
                this.setState({ translations });
            })
            .catch((error) => {
                console.error("Error loading translations:", error);
            });
    }

    loadCustomers() {
        axios.get(`/user/customers`).then((res) => {
            // Support both {data: [...]} and [...] responses
            const customers = Array.isArray(res.data) ? res.data : res.data.data || [];
            this.setState({ customers });
        }).catch((error) => {
            console.error('Error loading customers:', error);
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

    loadProducts(search = "") {
        const query = !!search ? `?search=${search}` : "";
        axios.get(`/user/products${query}`).then((res) => {
            const products = res.data.data;
            this.setState({ products });
        }).catch((error) => {
            console.error('Error loading products:', error);
        });
    }

    handleOnChangeBarcode(event) {
        const barcode = event.target.value;
        this.setState({ barcode });
    }

    loadCart() {
        const endpoint = this.state.isAdmin ? "/admin/cart" : "/user/user-cart";
        axios.get(endpoint).then((res) => {
            const cart = res.data;
            if(cart.length){
                const sub_total = this.getTotal(cart)
                const gr_total = sub_total - this.state.discount_amount
                const customer_id = cart[0].pivot.customer_id
                const branch_id = cart[0].pivot.branch_id
                const prev_balance = cart[0].pivot.user_balance
                const new_balance = cart[0].pivot.user_balance+gr_total
                const last_balance = cart[0].pivot.user_balance+gr_total
                this.setState({ cart, sub_total, gr_total,customer_id,branch_id, prev_balance, new_balance,last_balance });
            }else{
                const sub_total = 0
                const gr_total = 0
                const customer_id = ""
                const prev_balance = 0
                const new_balance = 0
                const last_balance = 0
                this.setState({ cart, sub_total, gr_total,customer_id, prev_balance, new_balance,last_balance });
            }


        }).catch((error) => {
            console.error('Error loading cart:', error);
        });
    }

    handleScanBarcode(event) {
        event.preventDefault();
        const { barcode } = this.state;

        if (!barcode) {
            Swal.fire('Please enter a barcode', 'warning');
            return false;
        }

        if (!this.canAddToCart()) {
            return false;
        }

        // For admin, use selectedBranchId if available, otherwise use branch_id
        const branchToUse = this.state.isAdmin && this.state.selectedBranchId ? this.state.selectedBranchId : this.state.branch_id;
        const endpoint = this.state.isAdmin ? "/admin/cart" : "/user/user-cart";
        axios
            .post(endpoint, { barcode, customer_id, branch_id: branchToUse })
                .then((res) => {
                    this.loadCart();
                    this.setState({ barcode: "" });
                })
                .catch((err) => {
                    Swal.fire("Error!", err.response.data.message, "error");
                });
    }

        handleChangeQty(product_id, qty) {
        // Only send to server if quantity is valid
        if (!qty || qty <= 0) {
            return;
        }

        // For admin, use selectedBranchId if available, otherwise use branch_id
        const branchToUse = this.state.isAdmin && this.state.selectedBranchId ? this.state.selectedBranchId : this.state.branch_id;
        const {customer_id} = this.state
        const endpoint = this.state.isAdmin ? "/admin/cart/change-qty" : "/user/user-cart/change-qty";

        axios
            .post(endpoint, { product_id, quantity: qty, customer_id, branch_id: branchToUse })
            .then((res) => {
                // Only update local state after successful server response
                const cart = this.state.cart.map((c) => {
                    if (c.id === product_id) {
                        c.pivot.quantity = qty;
                    }
                    return c;
                });

                // Update totals
                const sub_total = this.getTotal(cart)
                const gr_total = sub_total - this.state.discount_amount
                const new_balance = this.state.prev_balance + gr_total
                const last_balance = new_balance - this.state.paid_amount
                this.setState({ cart, sub_total, gr_total, new_balance, last_balance });
            })
            .catch((err) => {
                // Show error and reload cart to revert any changes
                this.loadCart();
                Swal.fire("Error!", err.response.data.message, "error");
            });
    }

    getTotal(cart) {
        if(isArray(cart)){
            const total = cart.map((c) => c.pivot.quantity * c.sell_price);
            return sum(total).toFixed(2);
        }
    }

    handleClickDelete(product_id) {
        // For admin, use selectedBranchId if available, otherwise use branch_id
        const branchToUse = this.state.isAdmin && this.state.selectedBranchId ? this.state.selectedBranchId : this.state.branch_id;
        const endpoint = this.state.isAdmin ? "/admin/cart/delete" : "/user/user-cart/delete";

        if (this.state.isAdmin) {
            // For admin, use DELETE method
            axios
                .delete(endpoint, { data: { product_id, branch_id: branchToUse } })
                .then((res) => {
                    const cart = this.state.cart.filter((c) => c.id !== product_id);
                    const sub_total = this.getTotal(cart)
                    const gr_total = sub_total - this.state.discount_amount
                    const new_balance = this.state.prev_balance + gr_total
                    const last_balance = new_balance - this.state.paid_amount

                    this.setState({
                        cart,
                        sub_total,
                        gr_total,
                        new_balance,
                        last_balance
                    });
                })
                .catch((err) => {
                    Swal.fire("Error!", "Failed to delete item from cart", "error");
                });
        } else {
            // For user, use POST with _method DELETE (Laravel form method spoofing)
            axios
                .post(endpoint, { product_id, _method: "DELETE", branch_id: branchToUse })
                .then((res) => {
                    const cart = this.state.cart.filter((c) => c.id !== product_id);
                    const sub_total = this.getTotal(cart)
                    const gr_total = sub_total - this.state.discount_amount
                    const new_balance = this.state.prev_balance + gr_total
                    const last_balance = new_balance - this.state.paid_amount

                    this.setState({
                        cart,
                        sub_total,
                        gr_total,
                        new_balance,
                        last_balance
                    });
                })
                .catch((err) => {
                    Swal.fire("Error!", "Failed to delete item from cart", "error");
                });
        }
    }

    handleEmptyCart() {
        const endpoint = this.state.isAdmin ? "/admin/cart/empty" : "/user/user-cart/empty";
        axios.post(endpoint, { _method: "DELETE" }).then((res) => {
            if(this.state.customer_id){
                const last_balance = this.state.prev_balance
                this.setState({ cart: [],sub_total:0, gr_total:0, new_balance:last_balance, last_balance, discount_amount:'', paid_amount:'' });
            }else{
                this.setState({ cart: [],sub_total:0, gr_total:0, new_balance:0, last_balance:0, discount_amount:'', paid_amount:'' });
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

    addProductToCart(barcode) {
        if (!this.canAddToCart()) {
            return false;
        }

        // For admin, use selectedBranchId if available, otherwise use branch_id
        const branchToUse = this.state.isAdmin && this.state.selectedBranchId ? this.state.selectedBranchId : this.state.branch_id;
        const customer_id = this.state.customer_id
        const branch_id = branchToUse
        let product = this.state.products.find((p) => p.barcode === barcode);
        const elements = document.querySelectorAll('[class*="product-"]');

        elements.forEach(el => {
            el.style.border = '0px';
        });
        const prodBarcode = document.getElementById('product-'+barcode);
        const prodInput = document.getElementById('prodinput-'+product.id);
        if(prodBarcode){
           prodBarcode.style.border = "2px solid #fcc";
        }

        if(prodInput){
            prodInput.style.border = "2px solid #fcc";
        }

        if (!!product) {
            const endpoint = this.state.isAdmin ? "/admin/cart" : "/user/user-cart";
            axios
                .post(endpoint, { barcode, customer_id, branch_id })
                .then((res) => {
                    // Only update local state after successful backend response
                    // if product is already in cart
                    let cart = this.state.cart.find((c) => c.id === product.id);
                    if (!!cart) {
                        // update quantity
                        const carts = this.state.cart.map((c) => {
                                if (
                                    c.id === product.id &&
                                    product.quantity > c.pivot.quantity
                                ) {
                                    c.pivot.quantity = c.pivot.quantity + 1;
                                }
                                return c;
                            })
                        const sub_total = this.getTotal(carts)
                        const gr_total = sub_total - this.state.discount_amount
                        const new_balance = this.state.prev_balance + this.state.gr_total
                        const last_balance = new_balance - this.state.discount_amount

                        this.setState({
                            cart: carts,
                            sub_total,
                            gr_total,
                            new_balance,
                            last_balance
                        });
                    } else {
                        if (product.quantity > 0) {
                            const newProduct = {
                                ...product,
                                pivot: {
                                    quantity: 1,
                                    product_id: product.id,
                                    user_id: 1,
                                },
                            };
                            const productList = [...this.state.cart, newProduct]
                            const sub_totals = this.getTotal(productList)
                            const gr_total = sub_totals - this.state.discount_amount
                            const new_balance = this.state.prev_balance + gr_total
                            const last_balance = new_balance - this.state.discount_amount
                            this.setState({
                                cart: productList,
                                sub_total: sub_totals,
                                gr_total,
                                new_balance,
                                last_balance
                            });
                        }
                    }
                })
                .catch((err) => {
                    Swal.fire("Error!", err.response.data.message, "error");
                });
        }


    }

    setCustomerId(event) {
        const customerData = event.target.value

        if(customerData){
            const customerInfo = this.state.customers.filter(cust=>cust.id==customerData)
            const new_balance = customerInfo[0].balance + this.state.gr_total
            const last_balance = new_balance - this.state.discount_amount
            this.setState({
                customer_id: customerInfo[0].id,
                selCustomerId: customerInfo[0].id,
                selCustomerFName: customerInfo[0].first_name,
                selCustomerLName: customerInfo[0].last_name,
                selCustomerAddress: customerInfo[0].address,
                selCustomerPhone: customerInfo[0].phone,
                selCustomerBalance: customerInfo[0].balance,
                prev_balance: customerInfo[0].balance,
                new_balance,
                last_balance
            }, () => {
                this.saveData(); // Save the customer selection
            });


        }else{
            this.setState({
                customer_id: '',
                selCustomerId: '',
                selCustomerFName: '',
                selCustomerLName: '',
                selCustomerAddress: '',
                selCustomerPhone: '',
                selCustomerBalance: '',
                prev_balance: '',
                new_balance:'',
                last_balance:''
            }, () => {
                this.saveData(); // Save the customer selection
            });
        }


    }

    setBranchId(event) {
        const branchData = event.target.value
        if(branchData){
            this.setState({
                branch_id: branchData
            });
        }else{
            this.setState({
                branch_id: '',
            });
        }


    }

            setSelectedBranchId(event) {
        const selectedBranchId = event.target.value;
        this.setState({ selectedBranchId }, () => {
            this.saveData(); // Save the selected branch
        });

        // If admin changes branch and has items in cart, validate stock
        if (this.state.isAdmin && selectedBranchId && this.state.cart.length > 0) {
            this.validateCartForNewBranch(selectedBranchId);
        }
    }

    canAddToCart() {
        if (!this.state.customer_id) {
            Swal.fire('Please select a customer first', 'warning');
            return false;
        }

        if (this.state.isAdmin) {
            if (!this.state.selectedBranchId) {
                Swal.fire('Please select a branch for POS first', 'warning');
                return false;
            }
        } else {
            if (!this.state.branch_id) {
                Swal.fire('Please select a branch first', 'warning');
                return false;
            }
        }

        return true;
    }

    validateCartForNewBranch(newBranchId) {
        const cart = this.state.cart;
        const branchStocks = this.state.branchStocks;

        for (let item of cart) {
            const productStocks = branchStocks[item.id];
            if (productStocks) {
                const branchStock = productStocks.find(stock => stock.branch_id == newBranchId);
                if (branchStock && branchStock.quantity < item.pivot.quantity) {
                    Swal.fire({
                        title: 'Insufficient Stock',
                        text: `Product "${item.name}" has only ${branchStock.quantity} stock in the selected branch, but cart has ${item.pivot.quantity}. Please reduce quantity or select a different branch.`,
                        icon: 'warning',
                        confirmButtonText: 'OK'
                    });
                    return false;
                }
            }
        }
        return true;
    }

    isProductInCart(productId) {
        return this.state.cart.some(item => item.id === productId);
    }



    printInvoice = () => {
        const invoiceUrl = `/user/sales/print/${this.state.saleId}`;
        window.open(invoiceUrl, "_blank");
    };

    handleClickSubmit() {
        if (!this.canAddToCart()) {
            return false;
        }

        // For admin, use selectedBranchId if available, otherwise use branch_id
        const branchToUse = this.state.isAdmin && this.state.selectedBranchId ? this.state.selectedBranchId : this.state.branch_id;

        // Validate cart stock before checkout
        if (this.state.isAdmin && !this.validateCartForNewBranch(branchToUse)) {
            return false;
        }
        Swal.fire({
            title: 'Save POS',
            input: "text",
            inputValue: this.state.paid_amount,
            showCancelButton: true,
            confirmButtonText: "Save Sale",
            cancelButtonText: this.state.translations["cancel_pay"],
            showLoaderOnConfirm: true,
            preConfirm: (amount) => {
                const salesEndpoint = this.state.isAdmin ? "/admin/sales" : "/user/sales";
                return axios
                    .post(salesEndpoint, {
                        customer_id: this.state.customer_id,
                        branch_id: branchToUse,
                        amount,
                        discount_amount: this.state.discount_amount,
                    })
                    .then((res) => {
                        this.loadCart();
                        // Assuming the response includes an order ID or invoice URL
                        const printUrl = this.state.isAdmin ? `/admin/sales/print/${res.data.id}` : `/user/sales/print/${res.data.id}`;
                        this.setState({ printUrl, paid_amount:0 }, () => {
                            // Clear saved data after successful sale
                            const cacheKey = this.getCacheKey();
                            localStorage.removeItem(cacheKey);
                        });
                        Swal.fire("Success", "Order has been saved!", "success");
                        return res.data;
                    })
                    .catch((err) => {
                        if (err.response && err.response.data && err.response.data.message) {
                            Swal.showValidationMessage(err.response.data.message);
                        } else {
                            Swal.showValidationMessage('An error occurred while processing the sale');
                        }
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
        }, () => {
            this.saveData(); // Save the discount amount
        })
    }

    changePaid = (e) =>{

        const new_balance = this.state.new_balance
        const paid_amount = e.target.value
        const last_balance = new_balance - paid_amount

        this.setState({
            paid_amount,
            new_balance,
            last_balance
        }, () => {
            this.saveData(); // Save the paid amount
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
        const { cart, products, customers, barcode, translations } = this.state;
        const cartList = Array.isArray(cart) ? cart : [];
        const productList = Array.isArray(products) ? products : [];
        const customerList = Array.isArray(customers) ? customers : [];
        const safeTranslations = translations || {};
        return (
            <div className="row">

                <div className="col-md-6 col-lg-6">
                    <div className="row mb-2">
                        <div className="col-md-3"><input type="text" onChange={this.setCustomerId} value={this.state.customer_id} name="customer-input" id="customer-input" className="form-control" placeholder="Enter customer ID or name" /></div>
                        <div className="col-md-4">
                            <select  onChange={this.setCustomerId}  id="sel-customer" className="form-control" value={this.state.customer_id} >
                                <option value="">Select a customer</option>
                                {customers.map((cus) => (
                                    <option
                                        key={cus.id}
                                        value={cus.id} >
                                        {`${cus.first_name} ${cus.last_name} - ${cus.address} `}
                                    </option>
                                ))}
                            </select>
                        </div>
                    </div>
                    <div className="row">
                        <div className="col-md-3"><span className="text-primary"><b>{this.state.selCustomerFName } {this.state.selCustomerLName}</b></span></div>
                        <div className="col-md-4"><span className="text-primary"><b style={{wordBreak: 'break-word', overflowWrap: 'break-word'}}>{this.state.selCustomerAddress}</b></span></div>
                        <div className="col-md-3"><span className="text-primary"><b style={{whiteSpace: 'nowrap'}}>{this.state.selCustomerPhone}</b></span></div>
                        <div className="col-md-2"><span className="text-primary"><b>{this.state.selCustomerBalance} BDT</b></span></div>
                    </div>
                    <div className="user-cart mt-1">
                        <div className="card" style={{ overflowY:'scroll' }}>
                            <table className="table table-striped">
                                <thead>
                                    <tr>
                                        <th>{safeTranslations["product_name"]}</th>
                                        <th>{safeTranslations["quantity"]}</th>
                                        <th className="text-right">
                                            {safeTranslations["price"]}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {cartList.map((c) => (
                                        <tr key={c.id}>
                                            <td>{c.name}</td>
                                            <td>
                                                <input
                                                    type="text"
                                                    className="form-control form-control-sm qty product-input"
                                                    defaultValue={c.pivot.quantity}
                                                    onBlur={(event) =>
                                                        this.handleChangeQty(
                                                            c.id,
                                                            event.target.value
                                                        )
                                                    }
                                                    onKeyPress={(event) => {
                                                        if (event.key === 'Enter') {
                                                            this.handleChangeQty(
                                                                c.id,
                                                                event.target.value
                                                            );
                                                        }
                                                    }}

                                                    id={'prodinput-'+c.id}
                                                />
                                                <button
                                                    className="btn btn-danger btn-sm"
                                                    onClick={() =>
                                                        this.handleClickDelete(
                                                            c.id
                                                        )
                                                    }
                                                >
                                                    <i className="fas fa-trash"></i>
                                                </button>
                                            </td>
                                            <td className="text-right">
                                                {window.APP.currency_symbol}{" "}
                                                {(
                                                    c.sell_price * c.pivot.quantity
                                                ).toFixed(2)}
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
                            {window.APP.currency_symbol} {this.numberFormat(this.state.sub_total)}
                        </div>
                        <div className="col">
                            <div className="font-weight-bold">Discount (0.00)</div>
                            <input type="text" onChange={this.changeDiscount} placeholder="Discount amount" name="discount" id="discount" className="form-control form-sm text-right"/>
                        </div>
                        <div className="col text-right">
                            <div className="font-weight-bold">Gr. Total </div>
                            <div>{window.APP.currency_symbol} {this.numberFormat(this.state.gr_total)}</div>
                        </div>
                    </div>
                     <div className="row">
                        <div className="col">
                            <div className="font-weight-bold">Total Balance</div>
                            <input type="text" value={this.numberFormat(this.state.new_balance)} readOnly name="discount" id="discount" className="form-control form-sm text-right"/>
                        </div>
                        <div className="col">
                            <div className="font-weight-bold">Paid Amount</div>
                            <input type="text"  onChange={this.changePaid}  value={this.state.paid_amount} name="discount" id="discount" placeholder="Paid amount" className="form-control form-sm text-right"/>
                        </div>
                        <div className="col text-right">
                            <div className="font-weight-bold">Last Balance </div>
                            <input type="text" value={this.numberFormat(this.state.last_balance)} readOnly name="discount" id="discount" className="form-control form-sm text-right"/>
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
                                        🖨️ Print Invoice
                                    </a>

                            ): (

                                    <a


                                        rel="noopener noreferrer"
                                        className="btn btn-success btn-block"
                                    >
                                        🖨️ Print Invoice
                                    </a>

                            )}

                        </div>
                        <div className="col">
                            <button
                                type="button"
                                className="btn btn-danger btn-block"
                                onClick={this.handleEmptyCart}
                                disabled={!cartList.length}
                            >
                                {safeTranslations["cancel"]}
                            </button>
                        </div>
                        <div className="col">
                            <button
                                type="button"
                                className="btn btn-primary btn-block"
                                disabled={!cartList.length}
                                onClick={this.handleClickSubmit}
                            >
                                {safeTranslations["checkout"]}
                            </button>
                        </div>

                    </div>
                </div>


                <div className="col-md-6 col-lg-6">
                    {/* Branch Selection for Admin */}
                    {this.state.isAdmin && (
                        <div className="row mb-3">
                            <div className="col-12">
                                <label htmlFor="pos-branch-select" style={{ fontWeight: 'bold', marginBottom: '5px', display: 'block' }}>
                                    Select Branch for POS:
                                </label>
                                                                <select
                                    id="pos-branch-select"
                                    className="form-control"
                                    value={this.state.selectedBranchId}
                                    onChange={this.setSelectedBranchId}
                                    style={{ marginBottom: '10px' }}
                                >
                                    <option value="">-- Select Branch for POS --</option>
                                    {this.state.branches.map((branch) => (
                                        <option key={branch.id} value={branch.id}>
                                            {branch.branch_name}
                                        </option>
                                    ))}
                                </select>
                            </div>
                        </div>
                    )}

                    <div className="row">
                        <div className="col">
                            <form onSubmit={this.handleScanBarcode}>
                                <input
                                    type="text"
                                    className="form-control"
                                    placeholder={safeTranslations["scan_barcode"]}
                                    value={barcode}
                                    onChange={this.handleOnChangeBarcode}
                                />
                            </form>
                        </div>

                        <div className="col-lg-8 mb-2">
                            <input
                                type="text"
                                className="form-control"
                                placeholder={(safeTranslations["search_product"] || "Search") + "..."}
                                onChange={this.handleChangeSearch}
                                onKeyDown={this.handleSeach}
                            />
                        </div>

                    </div>



                    <div className="order-product">
                        {productList.map((p) => {
                            // Get branch stocks for this product if admin
                            const productBranchStocks = this.state.isAdmin && this.state.branchStocks[p.id] ? this.state.branchStocks[p.id] : [];
                            const isInCart = this.isProductInCart(p.id);

                            return (
                                <div
                                    onClick={() => this.addProductToCart(p.barcode)}
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
                                                // Determine color: blue for selected branch, red for low stock, green for good stock
                                                let textColor = 'green'; // Default: green for sufficient stock
                                                let fontWeight = 'normal';

                                                const warningQty = 20; // Warning threshold set to 20
                                                const stockQty = parseInt(stock.quantity);
                                                const isOutOfStock = stockQty === 0;
                                                const isLowStock = stockQty > 0 && stockQty < warningQty;

                                                if (this.state.selectedBranchId && parseInt(stock.branch_id) === parseInt(this.state.selectedBranchId)) {
                                                    textColor = '#007bff'; // Blue for selected branch
                                                    fontWeight = 'bold';
                                                } else if (isOutOfStock) {
                                                    textColor = 'red'; // Red for out of stock (0)
                                                } else if (isLowStock) {
                                                    textColor = '#ff8c00'; // Orange/warning for low stock (1-14)
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
                                                        <span style={{ fontWeight: 'bold' }}>{stock.quantity}</span>
                                                    </div>
                                                );
                                            })}
                                        </div>
                                    ) : (
                                        <div style={{
                                            fontSize: '14px',
                                            color: (() => {
                                                const stockQty = parseInt(p.quantity);
                                                if (stockQty === 0) return 'red'; // Red for out of stock
                                                if (stockQty < 20) return '#ff8c00'; // Orange for low stock (under 20)
                                                return 'green'; // Green for sufficient stock (20+)
                                            })(),
                                            fontWeight: 'bold'
                                        }}>
                                            Stock: {p.quantity}
                                        </div>
                                    )}
                                </div>
                            );
                        })}
                    </div>
                </div>



            </div>
        );
    }
}

export default Cart;

const root = document.getElementById("pos-cart");
if (root) {
    const rootInstance = createRoot(root);
    rootInstance.render(<Cart />);
}
