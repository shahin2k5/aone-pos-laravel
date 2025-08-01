import React, { Component } from "react";
import { createRoot } from "react-dom/client";
import axios from "axios";
import Swal from "sweetalert2";
import { isArray, sum } from "lodash";

class PurchaseCart extends Component {
    constructor(props) {
        super(props);
        this.state = {
            cart: [],
            products: [],
            suppliers: Array.isArray(props && props.suppliers) ? props.suppliers : [],
            barcode: "",
            search: "",
            supplier_id: "",
            translations: {},
            sub_total:0,
            discount_amount:0,
            gr_total:0,
            prev_balance:0,
            new_balance:0,
            paid_amount:0,
            last_balance:0,
            selCustomerId:'',
            selCustomerFName:'',
            selCustomerLName:'',
            selCustomerAddress:'',
            selCustomerPhone:'',
            selCustomerBalance:'',
            printUrl:'',
            supplier_invoice_no:'',
            branches: [],
            branch_id: '',
            branch_quantities: {}, // { product_id: { branch_id: quantity, ... }, ... }
            error: '',
        };

        this.loadCart = this.loadCart.bind(this);
        this.handleOnChangeBarcode = this.handleOnChangeBarcode.bind(this);
        this.handleScanBarcode = this.handleScanBarcode.bind(this);
        this.handleChangeQty = this.handleChangeQty.bind(this);
        this.handleEmptyCart = this.handleEmptyCart.bind(this);

        this.loadProducts = this.loadProducts.bind(this);
        this.handleChangeSearch = this.handleChangeSearch.bind(this);
        this.handleSeach = this.handleSeach.bind(this);
        this.setSupplierId = this.setSupplierId.bind(this);
        this.setSupplierInvoiceNo = this.setSupplierInvoiceNo.bind(this);
        this.handleClickSubmit = this.handleClickSubmit.bind(this);
        this.loadTranslations = this.loadTranslations.bind(this);
        this.setBranchId = this.setBranchId.bind(this);
        this.setBranchQuantity = this.setBranchQuantity.bind(this);
    }

    componentDidMount() {
        // load user cart
        this.loadTranslations();
        this.loadCart();
        this.loadProducts();
        this.loadsuppliers();
        this.loadBranches();

    }

    // load the transaltions for the react component
    loadTranslations() {
        axios
            .get("/admin/locale/cart")
            .then((res) => {
                const translations = res.data;
                this.setState({ translations });
            })
            .catch((error) => {
                console.error("Error loading translations:", error);
            });
    }

    loadsuppliers() {
        axios.get(`/admin/suppliers`).then((res) => {
            const suppliers = res.data;
            this.setState({ suppliers });
        });
    }

    loadProducts(search = "") {
        const query = !!search ? `?search=${search}` : "";
        axios.get(`/admin/products${query}`).then((res) => {
            const products = res.data.data;
            this.setState({ products });
        });
    }

    loadBranches() {
        axios.get(`/admin/branches`).then((res) => {
            console.log('API /admin/branches response:', res.data);
            const branches = res.data;
            this.setState({ branches, error: '' }, () => {
                console.log('Branches in state:', this.state.branches);
            });
        }).catch((error) => {
            console.error('Error loading branches:', error);
            this.setState({ error: 'Failed to load branches: ' + (error.response?.status || error.message) });
        });
    }

    handleOnChangeBarcode(event) {
        const barcode = event.target.value;
        console.log(barcode);
        this.setState({ barcode });
    }

    loadCart() {
        axios.get("/admin/purchasecart").then((res) => {
            const cart = res.data;
            console.log('load purchase list:', cart)
            const discount_amount = 0
            const paid_amount = 0
            if(cart.length){
                const sub_total = this.getTotal(cart)
                const gr_total = sub_total - this.state.discount_amount
                const supplier_id = cart[0].pivot.supplier_id
                const supplier_invoice_no = cart[0].pivot.supplier_invoice_id
                const prev_balance = cart[0].pivot.user_balance
                const new_balance = parseFloat(cart[0].pivot.user_balance) + parseFloat(gr_total)
                const last_balance = parseFloat(cart[0].pivot.user_balance)+parseFloat(gr_total)

                this.setState({ cart,
                                sub_total,
                                gr_total,
                                supplier_id,
                                prev_balance,
                                new_balance,
                                last_balance,
                                discount_amount,
                                paid_amount,
                                supplier_invoice_no });
            }else{
                const sub_total = 0
                const gr_total = 0
                const supplier_id = ""
                const supplier_invoice_no = ""
                const prev_balance = 0
                const new_balance = 0
                const last_balance = 0
                this.setState({ cart, sub_total, gr_total,supplier_id,  discount_amount,
                                paid_amount,
                                prev_balance, new_balance,last_balance,supplier_invoice_no });
            }

        });
    }

    handleScanBarcode(event) {
        event.preventDefault();
        const { barcode, supplier_id, supplier_invoice_no } = this.state;
        if (!!barcode && !!supplier_id) {
            axios
                .post("/admin/purchase-cart", { barcode, supplier_id,supplier_invoice_no, branch_id: this.state.branch_id })
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
        const cart = this.state.cart.map((c) => {
            if (c.id === product_id) {
                c.pivot.qnty = qty;
            }
            return c;
        });

        if (!qty) return;

        axios
            .post("/admin/purchase-cart/change-qty", { product_id, quantity: qty })
            .then((res) => {
                const sub_total = this.getTotal(cart)
                const gr_total = sub_total - this.state.discount_amount
                const new_balance = parseFloat(this.state.prev_balance) + parseFloat(gr_total)
                const last_balance = new_balance - this.state.paid_amount
                this.setState({ cart, sub_total, gr_total, new_balance, last_balance });
            })
            .catch((err) => {
                Swal.fire("Error!", err.response.data.message, "error");
            });
    }

    handlePurchasePrice(product_id, purchase_price) {
        if(purchase_price<=0){
            return
        }
        const cart = this.state.cart.map((c) => {
            if (c.id === product_id) {
                c.pivot.purchase_price = purchase_price;
                c.purchase_price = purchase_price;
            }
            return c;
        });

        if (!purchase_price) return;

        axios
            .post("/admin/purchase-cart/change-purchaseprice", { product_id, purchase_price  })
            .then((res) => {
                const sub_total = this.getTotal(cart)
                const gr_total = sub_total - this.state.discount_amount
                const new_balance = parseFloat(this.state.prev_balance)+parseFloat(gr_total)
                const last_balance = new_balance - this.state.paid_amount
                this.setState({ cart, sub_total, gr_total, new_balance, last_balance });
            })
            .catch((err) => {
                Swal.fire("Error!", err.response.data.message, "error");
            });
    }

    getTotal(cart) {
        if(isArray(cart)){
            const total = cart.map((c) => c.pivot.qnty * c.pivot.purchase_price);
            return sum(total).toFixed(2);
        }
    }

    handleClickDelete(product_id) {
        axios
            .post("/admin/purchase-cart/delete", { product_id, _method: "DELETE" })
            .then((res) => {
                const cart = this.state.cart.filter((c) => c.id !== product_id);
                const sub_total = this.getTotal(cart)
                const gr_total = sub_total - this.state.discount_amount
                const new_balance = parseFloat(this.state.prev_balance) + parseFloat(gr_total)
                const last_balance = new_balance - this.state.discount_amount

                this.setState({
                    cart,
                    sub_total,
                    gr_total,
                    new_balance,
                    last_balance
                });
            });
    }

    handleEmptyCart() {
        axios.post("/admin/purchase-cart/empty", { _method: "DELETE" }).then((res) => {
            if(this.state.supplier_id){
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
        if(!this.state.supplier_id){
            Swal.fire('Please select a supplier', 'warning');
            return false
        }
        const supplier_id = this.state.supplier_id
        const supplier_invoice_no = this.state.supplier_invoice_no
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
            // if product is already in cart
            let cart = this.state.cart.find((c) => c.id === product.id);
            if (!!cart) {
                // update quantity
                const carts = this.state.cart.map((c) => {
                        if (
                            c.id === product.id
                        ) {
                            c.pivot.qnty = parseInt(c.pivot.qnty) + 1;
                            c.purchase_price = c.pivot.purchase_price
                        }
                        return c;
                    })
                const sub_total = this.getTotal(carts)
                const gr_total = sub_total - this.state.discount_amount
                const new_balance = parseFloat(this.state.prev_balance) + parseFloat(this.state.gr_total)
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
                    product = {
                        ...product,
                        pivot: {
                            qnty: 1,
                            product_id: product.id,
                            user_id: 1,
                             purchase_price : product.purchase_price
                        },
                    };
                    const productList = [...this.state.cart, product]
                    const sub_totals = this.getTotal(productList)
                    const gr_total = sub_totals - this.state.discount_amount
                    const new_balance = parseFloat(this.state.prev_balance) + parseFloat(gr_total)
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



            const branch_quantities = this.state.branch_quantities[product.id] || {};
            axios
                .post("/admin/purchase-cart", { barcode, supplier_id, supplier_invoice_no, branch_quantities })
                .then((res) => {
                    this.loadCart(); // Ensure cart is reloaded after adding
                    console.log(res);
                    this.setState({ error: '' });
                })
                .catch((err) => {
                    console.error('Error adding product to cart:', err);
                    this.setState({ error: 'Failed to add product to cart: ' + (err.response?.data?.message || err.message) });
                    Swal.fire("Error!", err.response?.data?.message || err.message, "error");
                });
        }


    }

    setSupplierInvoiceNo = (e) =>{
        this.setState({
            supplier_invoice_no:e.target.value
        })
    }

    setSupplierId(event) {
        const customerData = event.target.value

        if(customerData){
            const customerInfo = this.state.suppliers.filter(cust=>cust.id==customerData)
            const new_balance = parseFloat(customerInfo[0].balance) + parseFloat(this.state.gr_total)
            const last_balance = new_balance - this.state.discount_amount
            this.setState({
                supplier_id: customerInfo[0].id,
                selCustomerId: customerInfo[0].id,
                selCustomerFName: customerInfo[0].first_name,
                selCustomerLName: customerInfo[0].last_name,
                selCustomerAddress: customerInfo[0].address,
                selCustomerPhone: customerInfo[0].phone,
                selCustomerBalance: customerInfo[0].balance,
                prev_balance: customerInfo[0].balance,
                new_balance,
                last_balance
            });

            console.log('customerData',customerInfo)
        }else{
            this.setState({
                supplier_id: '',
                selCustomerId: '',
                selCustomerFName: '',
                selCustomerLName: '',
                selCustomerAddress: '',
                selCustomerPhone: '',
                selCustomerBalance: '',
                prev_balance: '0',
                new_balance:'',
                last_balance:''
            });
        }


    }

    setBranchId(event) {
        const branch_id = event.target.value;
        this.setState({ branch_id });
    }

    setBranchQuantity(product_id, branch_id, value) {
        this.setState((prevState) => {
            const branch_quantities = { ...prevState.branch_quantities };
            if (!branch_quantities[product_id]) branch_quantities[product_id] = {};
            branch_quantities[product_id][branch_id] = Number(value);
            return { branch_quantities };
        });
    }

    printInvoice = () => {
        const invoiceUrl = `/admin/purchase/print/${this.state.saleId}`;
        window.open(invoiceUrl, "_blank");
    };

    handleClickSubmit() {
        if(!this.state.supplier_id){
            Swal.fire('Please select a supplier', 'warning');
            return false
        }
        const sub_total = this.state.sub_total
        const discount_amount = this.state.discount_amount
        const gr_total = this.state.gr_total
        const paid_amount = this.state.paid_amount

        Swal.fire({
            title: 'Save Purchase Invocie',
            input: "text",
            inputValue: paid_amount,
            showCancelButton: true,
            confirmButtonText: "Save Purchases",
            cancelButtonText: this.state.translations["cancel_pay"],
            showLoaderOnConfirm: true,
            preConfirm: (amount) => {
                return axios
                    .post("/admin/purchase", {
                        supplier_id: this.state.supplier_id,
                        sub_total,
                        discount_amount,
                        gr_total,
                        paid_amount:amount,
                        amount
                    })
                    .then((res) => {
                        this.loadCart();
                        // Assuming the response includes an order ID or invoice URL
                        const printUrl = `/admin/purchase/print/${res.data.id}`;
                        this.setState({ printUrl, paid_amount:0, discount_amount:0,supplier_id:'',supplier_invoice_no:'' });
                        Swal.fire("Success", "Purchase has been saved!", "success");
                        window.reload();
                        return res.data;
                    })
                    .catch((err) => {
                        console.log('error:::', err.response)
                        Swal.showValidationMessage(err.response.data.message);
                    });
            },
            allowOutsideClick: () => !Swal.isLoading(),
        }).then((result) => {
            window.reload();
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
        const discount_amount = parseFloat(e.target.value)
        const gr_total = this.state.sub_total - discount_amount
        const new_balance = parseFloat(this.state.prev_balance) + parseFloat(gr_total)
        const last_balance = parseFloat(new_balance) - this.state.paid_amount

        console.log('last bal + new bal + paid amount::', last_balance + '' + new_balance + ' ' + this.state.paid_amount)
        this.setState({
            discount_amount,
            gr_total,
            new_balance,
            last_balance
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
        // Remove suppliers from destructuring
        const { cart, products, barcode, translations } = this.state;
        const suppliers = Array.isArray(this.state.suppliers) ? this.state.suppliers : [];
        console.log('Rendering branches:', this.state.branches);
        return (
            <div className="row">

                <div className="col-md-6 col-lg-6">
                    <div className="row mb-2">
                        <div className="col-md-3">
                            <b>Supplier Invoice</b>
                            <input type="text"
                                onChange={this.setSupplierInvoiceNo}
                                value={this.state.supplier_invoice_no}
                                name="invoice-input"
                                id="invoice-input"
                                placeholder="Invoice no"
                                className="form-control"></input>
                        </div>
                        <div className="col-md-9">
                            <b>Supplier Name</b>
                            <select  onChange={this.setSupplierId}  id="sel-customer" className="form-control"  >
                                <option value="">Select a supplier</option>
                                {suppliers.map((sup) => (
                                    <option
                                        key={sup.id}
                                        selected={sup.id==this.state.supplier_id}
                                        value={sup.id} >
                                        {`${sup.first_name} ${sup.last_name} - ${sup.address} `}
                                    </option>
                                ))}
                            </select>
                        </div>
                        <div className="col">

                        </div>
                    </div>
                    <div className="row">
                        <div className="col-md-3"><span className="text-primary"><b>{this.state.selCustomerFName } {this.state.selCustomerLName}</b></span></div>
                        <div className="col-md-4"><span className="text-primary"><b style={{wordBreak: 'break-word', overflowWrap: 'break-word'}}>{this.state.selCustomerAddress}</b></span></div>
                        <div className="col-md-3"><span className="text-primary"><b style={{whiteSpace: 'nowrap'}}>{this.state.selCustomerPhone}</b></span></div>
                        <div className="col-md-2"><span className="text-primary"><b>Balance: {this.state.selCustomerBalance} BDT</b></span></div>
                    </div>
                    <div className="user-cart mt-1">
                        <div className="card">
                            <div style={{overflowX: 'auto'}}>
                                <table className="table table-striped" style={{tableLayout: 'fixed', width: '100%'}}>
                                    <thead>
                                        <tr>
                                            <th style={{width: '25%'}}>{translations["product_name"]}</th>
                                            <th style={{width: '20%'}}>Branch</th>
                                            <th style={{width: '15%'}}>{translations["quantity"]}</th>
                                            <th className="text-right" style={{width: '20%'}}>Purchase Rate</th>
                                            <th className="text-right" style={{width: '20%'}}>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {cart.map((c, idx) => {
                                            // Ensure both IDs are strings for comparison
                                            const branch = this.state.branches.find(b => String(b.id) === String(c.pivot.branch_id));
                                            if (!branch) return null; // Only render if branch is found
                                            return (
                                                <tr key={c.id + '-' + c.pivot.branch_id}>
                                                    <td>{c.name}</td>
                                                    <td>{branch.branch_name}</td>
                                                    <td>
                                                        <input
                                                            type="text"
                                                            className="form-control form-control-sm qty product-input"
                                                            value={c.pivot.qnty}
                                                            onChange={(event) =>
                                                                this.handleChangeQty(
                                                                    c.id,
                                                                    event.target.value
                                                                )
                                                            }
                                                            id={'prodinput-'+c.id+'-'+c.pivot.branch_id}
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
                                                    <td width={'120px'}>
                                                        <div className="input-group input-group-sm mb-1">
                                                            <div className="input-group-prepend">
                                                                <span className="input-group-text" id="basic-addon1">{window.APP.currency_symbol}{" "}</span>
                                                            </div>
                                                            <input type="text" className="form-control input-sm"
                                                                placeholder="Purchase Price"
                                                                onChange={(event) =>
                                                                    this.handlePurchasePrice(
                                                                        c.id,
                                                                        event.target.value
                                                                    )
                                                                }
                                                                value={c.purchase_price}
                                                            />
                                                        </div>
                                                    </td>
                                                    <td className="text-right">
                                                        {window.APP.currency_symbol}{" "}
                                                        {(c.purchase_price * c.pivot.qnty).toFixed(2)}
                                                    </td>
                                                </tr>
                                            );
                                        })}
                                    </tbody>
                                </table>
                            </div>
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
                                        className="btn btn-default btn-block border"
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
                                disabled={!cart.length}
                            >
                                {translations["cancel"]}
                            </button>
                        </div>
                        <div className="col">
                            <button
                                type="button"
                                className="btn btn-primary btn-block"
                                disabled={!cart.length}
                                onClick={this.handleClickSubmit}
                            >
                                Make Purchase
                            </button>
                        </div>

                    </div>
                </div>


                <div className="col-md-6 col-lg-6">
                    <div className="row">
                        <div className="col">
                            <b>Product Code</b>
                            <form onSubmit={this.handleScanBarcode}>
                                <input
                                    type="text"
                                    className="form-control"
                                    placeholder={translations["scan_barcode"]}
                                    value={barcode}
                                    onChange={this.handleOnChangeBarcode}
                                />
                            </form>
                        </div>

                        <div className="col-lg-8 mb-2">
                            <b>Product Name</b>
                            <input
                                type="text"
                                className="form-control"
                                placeholder={translations["search_product"] + "..."}
                                onChange={this.handleChangeSearch}
                                onKeyDown={this.handleSeach}
                            />
                        </div>

                    </div>

                    <div className="mb-3">
                        <h5 style={{ marginBottom: '10px', color: '#333', fontWeight: 'bold' }}>
                            Available Products ({products.length})
                        </h5>
                    </div>

                    <div className="order-product">
                        {products.map((p) => {
                            // Prepare stock details for tooltip
                            const stockDetails = this.state.branches.map(branch => {
                                const stock = p.branch_stocks && p.branch_stocks[branch.id] !== undefined ? p.branch_stocks[branch.id] : 'N/A';
                                return `${branch.branch_name}: ${stock}`;
                            }).join('\n'); // Use newlines for better readability

                            return (
                                <div key={p.id} className="item product-div" id={'product-'+p.barcode} style={{
                                    width: '240px',
                                    minHeight: '340px',
                                    border: '1px solid #eee',
                                    borderRadius: '10px',
                                    padding: '16px',
                                    margin: '10px',
                                    boxShadow: '0 2px 8px rgba(0,0,0,0.04)',
                                    display: 'inline-block',
                                    verticalAlign: 'top',
                                    background: '#fff',
                                    position: 'relative'
                                }}>
                                    <img
                                        src={p.image_url}
                                        alt={p.name}
                                        onError={e => { e.target.onerror = null; e.target.src = '/images/img-placeholder.jpg'; }}
                                        style={{
                                            width: '160px',
                                            height: '120px',
                                            objectFit: 'cover',
                                            borderRadius: '8px',
                                            border: '1px solid #eee',
                                            margin: '0 auto',
                                            display: 'block',
                                        }}
                                    />
                                    <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', marginTop: '8px', marginBottom: '4px' }}>
                                        <span
                                            style={{
                                                fontWeight: 'bold',
                                                color: 'black',
                                                textAlign: 'center',
                                                whiteSpace: 'nowrap',
                                                overflow: 'hidden',
                                                textOverflow: 'ellipsis',
                                                maxWidth: '120px',
                                                display: 'inline-block',
                                            }}
                                            title={p.name}
                                        >
                                            {p.name}
                                        </span>
                                        {/* Info icon with tooltip */}
                                        <span
                                            tabIndex={0}
                                            style={{
                                                marginLeft: '6px',
                                                cursor: 'pointer',
                                                color: '#007bff',
                                                fontSize: '16px',
                                                outline: 'none',
                                                position: 'relative',
                                                display: 'inline-block',
                                            }}
                                            aria-label="Show available stock details"
                                            onFocus={e => {
                                                const tooltip = e.target.querySelector('.stock-tooltip');
                                                if (tooltip) tooltip.style.display = 'block';
                                            }}
                                            onBlur={e => {
                                                const tooltip = e.target.querySelector('.stock-tooltip');
                                                if (tooltip) tooltip.style.display = 'none';
                                            }}
                                            onMouseEnter={e => {
                                                const tooltip = e.currentTarget.querySelector('.stock-tooltip');
                                                if (tooltip) tooltip.style.display = 'block';
                                            }}
                                            onMouseLeave={e => {
                                                const tooltip = e.currentTarget.querySelector('.stock-tooltip');
                                                if (tooltip) tooltip.style.display = 'none';
                                            }}
                                        >
                                            <span style={{fontWeight: 'bold'}}>ℹ️</span>
                                            <span
                                                className="stock-tooltip"
                                                style={{
                                                    display: 'none',
                                                    position: 'absolute',
                                                    left: '50%',
                                                    top: '120%',
                                                    transform: 'translateX(-50%)',
                                                    background: '#222',
                                                    color: '#fff',
                                                    padding: '8px 12px',
                                                    borderRadius: '6px',
                                                    fontSize: '13px',
                                                    whiteSpace: 'pre-line',
                                                    zIndex: 10,
                                                    minWidth: '180px',
                                                    maxWidth: '260px',
                                                    boxShadow: '0 2px 8px rgba(0,0,0,0.15)',
                                                    pointerEvents: 'none',
                                                }}
                                                role="tooltip"
                                            >
                                                <b>Available Stock:</b>\n{stockDetails}
                                            </span>
                                        </span>
                                    </div>
                                    <div>
                                        {this.state.branches.map((branch) => (
                                            <div key={branch.id} style={{marginBottom: '4px', display: 'flex', alignItems: 'center'}}>
                                                <label style={{minWidth: '80px'}}>{branch.branch_name}</label>
                                                <input
                                                    type="number"
                                                    min="0"
                                                    value={this.state.branch_quantities[p.id]?.[branch.id] || ''}
                                                    onChange={e => this.setBranchQuantity(p.id, branch.id, e.target.value)}
                                                    style={{width: '60px', marginLeft: '8px'}}
                                                />
                                            </div>
                                        ))}
                                    </div>
                                    <button onClick={() => this.addProductToCart(p.barcode)} className="btn btn-primary btn-sm mt-2" style={{width: '100%'}}>Add to Cart</button>
                                </div>
                            );
                        })}
                    </div>
                    {this.state.error && (
                        <div className="alert alert-danger">{this.state.error}</div>
                    )}
                </div>



            </div>
        );
    }
}

export default PurchaseCart;

const mountPurchaseCart = () => {
    const root = document.getElementById("purchase-cart");
    if (root) {
        const rootInstance = createRoot(root);
        rootInstance.render(<PurchaseCart />);
    }
};

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", mountPurchaseCart);
} else {
    mountPurchaseCart();
}
