import React, { Component } from "react";
import { createRoot } from "react-dom";
import axios from "axios";
import Swal from "sweetalert2";
import { isArray, sum } from "lodash";

class Purchasereturn extends Component {
    constructor(props) {
        super(props);
        this.state = {
            cart: [],
            products: [],
            customers: [],
            barcode: "",
            search: "",
            customer_id: "",
            customer_info: "",
            translations: {},
            sub_total:0,
            discount_amount:0,
            gr_total:0,
            prev_balance:0,
            new_balance:0,
            return_amount:'',
            last_balance:0,
            selCustomerId:'',
            selCustomerFName:'',
            selCustomerLName:'',
            selCustomerAddress:'',
            selCustomerPhone:'',
            selCustomerBalance:'',
            printUrl:'',
            purchase_field:'purchase_id',
            isAdmin: window.APP && window.APP.user_role === 'admin', // Check if user is admin
            branches: [],
            branchStocks: {},
            supplier_id: '',
            purchase_id: '',
            supplierInfo: null,
            supplier: ''
        };

        this.loadCart = this.loadCart.bind(this);
        this.handleOnChangeBarcode = this.handleOnChangeBarcode.bind(this);
        this.handleScanBarcode = this.handleScanBarcode.bind(this);
        this.handleChangeQty = this.handleChangeQty.bind(this);
        this.handleEmptyCart = this.handleEmptyCart.bind(this);

        this.loadProducts = this.loadProducts.bind(this);
        this.handleChangeSearch = this.handleChangeSearch.bind(this);
        this.handleSeach = this.handleSeach.bind(this);
        this.findPurchaseID = this.findPurchaseID.bind(this);
        this.handleClickSubmit = this.handleClickSubmit.bind(this);
        this.loadTranslations = this.loadTranslations.bind(this);
        this.loadBranches = this.loadBranches.bind(this);
        this.loadBranchStocks = this.loadBranchStocks.bind(this);
    }

    componentDidMount() {
        // load user cart
        this.loadTranslations();
        this.loadCart();
        this.loadProducts();
        this.loadCustomers();
        this.loadBranches();
        this.loadBranchStocks();
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

    loadCustomers() {
        axios.get(`/admin/customers`).then((res) => {
            const customers = res.data;
            this.setState({ customers });
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
            const branches = res.data;
            this.setState({ branches });
        });
    }

    loadBranchStocks() {
        axios.get(`/admin/branch-stocks`).then((res) => {
            const branchStocks = res.data;
            this.setState({ branchStocks });
        });
    }

    handleOnChangeBarcode(event) {
        const barcode = event.target.value;
        console.log(barcode);
        this.setState({ barcode });
    }

    loadCart() {
        axios.get("/admin/purchasereturn/findpurchaseid/0").then((res) => {
            const cart = res.data.purchasereturn_items || [];
            const purchase = res.data.purchase;
            if(cart && cart.length){
                const sub_total = this.getTotal(cart)
                const gr_total = sub_total - this.state.discount_amount
                const customer_id = purchase.customer_id
                const prev_balance = purchase.customer.balance
                const new_balance = purchase.customer.balance+gr_total
                const last_balance = purchase.customer.balance+gr_total
                this.setState({ cart, sub_total, gr_total,customer_id, prev_balance, new_balance,last_balance });
            }else{
                const sub_total = 0
                const gr_total = 0
                const customer_id = ""
                const prev_balance = 0
                const new_balance = 0
                const last_balance = 0
                this.setState({ cart: [], sub_total, gr_total,customer_id, prev_balance, new_balance,last_balance });
            }

        }).catch((error) => {
            console.error("Error loading cart:", error);
            this.setState({ cart: [] });
        });
    }

    handleScanBarcode(event) {
        event.preventDefault();
        const { barcode, supplier_id } = this.state;
        if (!!barcode && !!supplier_id) {
            axios
                .post("/admin/purchasereturn/cart", { barcode, supplier_id })
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
        const cart = this.state.cart.map((c) => {
            if (c.product_id === product_id) {
                c.qnty = qty;
                c_product = c
            }
            return c;
        });

        if (!qty) return;

        axios
            .post("/admin/purchasereturn/changeqnty", { product_id, qnty: qty, purchase_price: c_product.purchase_price })
            .then((res) => {
                const sub_total = this.getTotal(cart)
                const gr_total = sub_total - this.state.discount_amount
                const new_balance = this.state.prev_balance + gr_total
                const last_balance = new_balance - this.state.return_amount
                this.setState({ cart, sub_total, gr_total, new_balance, last_balance });
            })
            .catch((err) => {
                Swal.fire("Error!", err.response.data.message, "error");
            });
    }

    getTotal(cart) {
        if(isArray(cart)){
            const total = cart.map((c) => c.qnty * c.purchase_price);
            return sum(total).toFixed(2);
        }
    }

    handleClickDelete(product_id) {
        console.log(product_id)
        axios
            .post("/admin/purchasereturn/delete", { product_id, _method: "POST" })
            .then((res) => {
                const cart = this.state.cart.filter((c) => c.product_id !== product_id);
                const sub_total = this.getTotal(cart)
                const gr_total = sub_total - this.state.discount_amount
                const new_balance = this.state.prev_balance + gr_total
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
        axios.post("/admin/purchasereturn/empty", { _method: "DELETE" }).then((res) => {
            if(this.state.customer_id){
                const last_balance = this.state.prev_balance
                this.setState({ cart: [],sub_total:0, gr_total:0, new_balance:last_balance, last_balance, discount_amount:'', return_amount:'' });
            }else{
                this.setState({ cart: [],sub_total:0, gr_total:0, new_balance:0, last_balance:0, discount_amount:'', return_amount:'' });
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

    addProductToCart(product_id) {
        if(!this.state.supplier_id){
            Swal.fire('Please select purchase', 'warning');
            return false
        }
        const supplier_id = this.state.supplier_id
        let product = this.state.products.find((p) => p.id === product_id);
        const elements = document.querySelectorAll('[class*="product-"]');
        const barcode = product.barcode
        elements.forEach(el => {
            el.style.border = '0px';
        });
        const prodBarcode = document.getElementById('product-'+product.barcode);
        const prodInput = document.getElementById('prodinput-'+product.id);
        if(prodBarcode){
           prodBarcode.style.border = "2px solid #fcc";
        }

        if(prodInput){
            prodInput.style.border = "2px solid #fcc";
        }

        if (!!product) {
            // if product is already in cart
            let cart = this.state.cart.find((c) => c.product_id === product.id);
            if (!!cart) {
                // update quantity
                const carts = this.state.cart.map((c) => {
                        if (
                            c.product_id === product.id
                        ) {
                            c.qnty = parseInt(c.qnty) + 1;
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

                console.log('cart if')
            } else {
                console.log('else product::', product)
                if (product.quantity > 0) {
                    const purchase_return_cart = {
                        created_at: "2025-06-13T18:07:17.000000Z",
                        id : 1,
                        product_id : product.id,
                        purchase_id : this.state.purchase_id,
                        purchase_price : product.purchase_price,
                        purchase_return_id : null,
                        qnty : 1,
                        sell_price : null,
                        supplier : this.state.supplierInfo,
                        supplier_id : this.state.supplier,
                        total_price : "0.00",
                        updated_at : "2025-06-13T18:07:17.000000Z",
                        user_id : 1,
                        product,
                        pivot: {
                            qnty: 1,
                            quantity: 1,
                            product_id: product.id,
                            user_id: 1,
                        },
                    };
                    const productList = [...this.state.cart, purchase_return_cart]
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



            axios
                .post("/admin/purchasereturn/cart", { product_id:product.id, barcode, supplier_id, purchase_id: this.state.purchase_id })
                .then((res) => {
                    // this.loadCart();
                    console.log(this.state.cart);
                    console.log(res);
                })
                .catch((err) => {
                    Swal.fire("Error!", err.response.data.message, "error");
                });
        }


    }

    selectPurchaseField = (event) =>{
        const purchase_field = event.target.value
        this.setState({purchase_field})
    }
    findPurchaseID(event) {
        const purchase_id = event.target.value
        const key_code = event.keyCode

        if(purchase_id && key_code == 13){

            axios.get(`/admin/purchasereturn/findpurchaseid/${purchase_id}`).then((res) => {
                const purchase = res.data.purchase;
                const purchasereturn_items = res.data.purchasereturn_items;
                console.log('purchase',purchase);
                console.log('purchasereturn_items',purchasereturn_items);

                if(purchase){

                    const sub_total = this.getTotal(purchasereturn_items)
                    const gr_total = sub_total - this.state.discount_amount
                    const supplier_id = purchase.supplier_id
                    const user_balance = purchase.supplier.balance
                    const prev_balance = user_balance
                    const new_balance = user_balance - gr_total
                    const last_balance = new_balance
                    this.setState({ purchase_id, sub_total, gr_total,supplier_id, prev_balance, new_balance,last_balance,
                        customer_info: purchase.supplier,
                        cart: purchasereturn_items,
                        selCustomerFName:purchase.supplier.first_name,
                        selCustomerLName:purchase.supplier.last_name,
                        selCustomerAddress:purchase.supplier.address,
                        selCustomerPhone:purchase.supplier.phone,
                        selCustomerBalance:purchase.supplier.balance,
                     });
                }else{
                    const purchase_id = ""
                    const sub_total = 0
                    const gr_total = 0
                    const customer_id = ""
                    const prev_balance = 0
                    const new_balance = 0
                    const last_balance = 0
                    this.setState({ purchase_id,  cart:[], sub_total, gr_total,customer_id, prev_balance, new_balance,last_balance });
                }



        });

        }


    }

    printInvoice = () => {
        if (this.state.printUrl) {
            window.open(this.state.printUrl, "_blank");
        }
    };

    handleClickSubmit() {
        if(!this.state.supplier_id){
            Swal.fire('Please select a purchase list', 'warning');
            return false
        }
        Swal.fire({
            title: 'Save Purchase Return',
            input: "text",
            inputValue: this.state.return_amount,
            showCancelButton: true,
            confirmButtonText: "Save Purchase Return",
            cancelButtonText: this.state.translations["cancel_pay"],
            showLoaderOnConfirm: true,
            preConfirm: (amount) => {
                return axios
                    .post("/admin/purchasereturn/finalsave", {
                        supplier_id: this.state.supplier_id,
                        purchase_id: this.state.purchase_id,
                        amount,
                    })
                    .then((res) => {
                        this.loadCart();
                        // Set the print URL for printing
                        const isAdmin = window.APP && window.APP.user_role === 'admin';
                        const printUrl = isAdmin ? `/admin/purchasereturn/print/${res.data.id}` : `/user/purchasereturn/print/${res.data.id}`;
                        this.setState({ printUrl, return_amount:0 });
                        Swal.fire("Success", "Purchase return has been saved!", "success");
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
        const last_balance = parseFloat(new_balance) - return_amount

        this.setState({
            return_amount,
            new_balance,
            last_balance
        })
        console.log('current bal::', this.state.prev_balance)
    }

    numberFormat = (amount) =>{
        if(amount>0) {
            return Number(amount).toFixed(2)
        }else{
            return 0.00
        }
    }

    render() {
        const { cart, products, supplier, barcode, translations } = this.state;

        return (
            <div className="row">

                <div className="col-md-6 col-lg-6">
                    <div className="row mb-2">
                        <div className="col-md-3">
                            <select onKeyUp={this.selectPurchaseField} value={this.state.order_id} name="purchase-field-input" id="purchase-field-input" className="form-control border">
                                <option value="purchase_id">Purchase ID</option>
                                <option value="supplier_invoice_id">Supplier Invoice ID</option>
                            </select>
                        </div>

                        <div className="col-md-2">
                            <input type="text" onKeyUp={this.findPurchaseID} value={this.state.order_id} placeholder="Search Purchases" name="purchase-id-input" id="purchase-id-input" className="form-control border"></input>
                        </div>
                        <div className="col-md-5"><span className="text-danger"><b>{this.state.selCustomerFName } {this.state.selCustomerLName}</b></span>, <span className="text-danger"><b style={{wordBreak: 'break-word', overflowWrap: 'break-word'}}>{this.state.selCustomerAddress}</b></span></div>
                        <div className="col-md-2"> <span className="text-danger"><b>{this.state.selCustomerBalance?this.state.selCustomerBalance+' BDT':''}</b></span> </div>
                    </div>

                    <div className="user-cart mt-1">
                        <div className="card">
                            <table className="table table-striped">
                                <thead>
                                    <tr>
                                        <th>{translations["product_name"]}</th>
                                        <th>{translations["quantity"]}</th>
                                        <th className="text-right">{translations["price"]}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {isArray(cart) && cart.map((c) => (
                                        <tr key={c.id}>
                                            <td>{c.product?c.product.name:''}</td>
                                            <td>
                                                <input
                                                    type="text"
                                                    className="form-control form-control-sm qty product-input"
                                                    value={c.qnty}
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
                                                {(
                                                    c.purchase_price * c.qnty
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

                            <div>{window.APP.currency_symbol} {this.numberFormat(this.state.gr_total)}</div>
                        </div>
                        <div className="col">
                            {/* <div className="font-weight-bold">Discount (0.00)</div>
                            <input type="text" readOnly={true} onChange={this.changeDiscount} placeholder="Discount amount" name="discount" id="discount" className="form-control form-sm text-right"/> */}

                            <div className="font-weight-bold">Return Amount</div>
                            <input type="text"  onChange={this.changeReturnAmount}  value={this.state.return_amount} name="discount" id="discount" placeholder="Return amount" className="form-control form-sm text-right"/>
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
                                    🖨️ Print Invoice
                                </a>
                            ) : (
                                <a
                                    href="#"
                                    rel="noopener noreferrer"
                                    className="btn btn-success btn-block"
                                    style={{ opacity: 0.5, pointerEvents: 'none' }}
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
                                disabled={!cart}
                            >
                                {translations["cancel"]}
                            </button>
                        </div>
                        <div className="col">
                            <button
                                type="button"
                                className="btn btn-primary btn-block"
                                disabled={!cart}
                                onClick={this.handleClickSubmit}
                            >
                                {"Confirm Pur. Return"}
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
                                    placeholder={translations["scan_barcode"]}
                                    value={barcode}
                                    onChange={this.handleOnChangeBarcode}
                                />
                            </form>
                        </div>

                        <div className="col-lg-8 mb-2">
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
                            // Get branch stocks for this product if admin
                            const productBranchStocks = this.state.isAdmin && this.state.branchStocks[p.id] ? this.state.branchStocks[p.id] : [];
                            const isInCart = this.state.cart.some(item => item.product_id === p.id);

                            return (
                                <div
                                    onClick={() => this.addProductToCart(p.id)}
                                    key={p.id}
                                    className="item product-div"
                                    id={'product-'+p.barcode}
                                    style={{
                                        width: '150px',
                                        overflow:'hidden',
                                        height: '180px',
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
    }
}

export default Purchasereturn;

const root = document.getElementById("purchasereturn-cart");
if (root) {
    const rootInstance = createRoot(root);
    rootInstance.render(<Purchasereturn />);
}
