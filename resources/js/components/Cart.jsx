import React, { Component } from "react";
import { createRoot } from "react-dom";
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
            barcode: "",
            search: "",
            customer_id: "",
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
            printUrl:''
        };

        this.loadCart = this.loadCart.bind(this);
        this.handleOnChangeBarcode = this.handleOnChangeBarcode.bind(this);
        this.handleScanBarcode = this.handleScanBarcode.bind(this);
        this.handleChangeQty = this.handleChangeQty.bind(this);
        this.handleEmptyCart = this.handleEmptyCart.bind(this);

        this.loadProducts = this.loadProducts.bind(this);
        this.handleChangeSearch = this.handleChangeSearch.bind(this);
        this.handleSeach = this.handleSeach.bind(this);
        this.setCustomerId = this.setCustomerId.bind(this);
        this.handleClickSubmit = this.handleClickSubmit.bind(this);
        this.loadTranslations = this.loadTranslations.bind(this);
    }

    componentDidMount() {
        // load user cart
        this.loadTranslations();
        this.loadCart();
        this.loadProducts();
        this.loadCustomers();
        
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

    handleOnChangeBarcode(event) {
        const barcode = event.target.value;
        console.log(barcode);
        this.setState({ barcode });
    }

    loadCart() {
        axios.get("/admin/cart").then((res) => {
            const cart = res.data;
            if(cart.length){
                const sub_total = this.getTotal(cart)
                const gr_total = sub_total - this.state.discount_amount 
                const customer_id = cart[0].pivot.customer_id
                const prev_balance = cart[0].pivot.user_balance
                const new_balance = cart[0].pivot.user_balance+gr_total
                const last_balance = cart[0].pivot.user_balance+gr_total
                this.setState({ cart, sub_total, gr_total,customer_id, prev_balance, new_balance,last_balance });
            }else{
                const sub_total = 0
                const gr_total = 0
                const customer_id = ""
                const prev_balance = 0
                const new_balance = 0
                const last_balance = 0
                this.setState({ cart, sub_total, gr_total,customer_id, prev_balance, new_balance,last_balance });
            }
            // const sub_total = this.getTotal(cart)
            // const gr_total = sub_total - this.state.discount_amount 
            // const customer_id = cart[0].pivot.customer_id
            // const prev_balance = cart[0].pivot.user_balance
            // const new_balance = cart[0].pivot.user_balance+gr_total
            // const last_balance = cart[0].pivot.user_balance+gr_total
            // this.setState({ cart, sub_total, gr_total,customer_id, prev_balance, new_balance,last_balance });
            // console.log('customer_id',customer_id)
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
        const cart = this.state.cart.map((c) => {
            if (c.id === product_id) {
                c.pivot.quantity = qty;
            }
            return c;
        });
       
        if (!qty) return;

        axios
            .post("/admin/cart/change-qty", { product_id, quantity: qty })
            .then((res) => {
                const sub_total = this.getTotal(cart)
                const gr_total = sub_total - this.state.discount_amount
                const new_balance = this.state.prev_balance+gr_total
                const last_balance = new_balance - this.state.paid_amount
                this.setState({ cart, sub_total, gr_total, new_balance, last_balance });
            })
            .catch((err) => {
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
        axios
            .post("/admin/cart/delete", { product_id, _method: "DELETE" })
            .then((res) => {
                const cart = this.state.cart.filter((c) => c.id !== product_id);
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
        axios.post("/admin/cart/empty", { _method: "DELETE" }).then((res) => {
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
        if(!this.state.customer_id){
            Swal.fire('Please select a customer', 'warning');
            return false
        }
        const customer_id = this.state.customer_id
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
                    product = {
                        ...product,
                        pivot: {
                            quantity: 1,
                            product_id: product.id,
                            user_id: 1,
                        },
                    };
                    const productList = [...this.state.cart, product] 
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
                .post("/admin/cart", { barcode, customer_id })
                .then((res) => {
                    // this.loadCart();
                    console.log(res);
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
            });
            
            console.log('customerData',customerInfo)
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
            });
        }

        
    }

    printInvoice = () => {
        const invoiceUrl = `/admin/orders/print/${this.state.saleId}`;
        window.open(invoiceUrl, "_blank");
    };

    handleClickSubmit() {
        if(!this.state.customer_id){
            Swal.fire('Please select a customer', 'warning');
            return false
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
                return axios
                    .post("/admin/orders", {
                        customer_id: this.state.customer_id,
                        amount,
                    })
                    .then((res) => {
                        this.loadCart();
                        // Assuming the response includes an order ID or invoice URL
                        const printUrl = `/admin/orders/print/${res.data.id}`;
                        this.setState({ printUrl, paid_amount:0 });
                        Swal.fire("Success", "Order has been saved!", "success");
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
        const { cart, products, customers, barcode, translations } = this.state;
        return (
            <div className="row">
               
                <div className="col-md-6 col-lg-6">
                    <div className="row mb-2">
                        <div className="col-md-3"><input type="text" onChange={this.setCustomerId} value={this.state.customer_id} name="customer-input" id="customer-input" className="form-control"></input></div>
                        <div className="col-md-9">
                            <select  onChange={this.setCustomerId}  id="sel-customer" className="form-control"  >
                                <option value="">Select a customer</option>
                                {customers.map((cus) => (
                                    <option
                                        key={cus.id}
                                        selected={cus.id==this.state.customer_id}
                                        value={cus.id} >
                                        {`${cus.first_name} ${cus.last_name} - ${cus.address} `} 
                                    </option>
                                ))}
                            </select>
                        </div>
                        <div className="col">
                            <input type="hidden" onChange={this.setCustomerId} value={this.state.customer_id} name="customer-input" id="customer-input" className="form-control"></input>
                            <datalist  id="sel-customer"  >
                                 
                                {customers.map((cus) => (
                                    <option
                                        key={cus.id}
                                        value={`${cus.id}-${cus.first_name} ${cus.last_name}`} >
                                        {`${cus.address} - ${cus.phone} - ${cus.balance}`} 
                                    </option>
                                ))}
                            </datalist>
                        </div>
                    </div>
                    <div className="row">
                        <div className="col-md-4"><span className="text-primary"><b>{this.state.selCustomerFName } {this.state.selCustomerLName}</b></span></div>
                        <div className="col-md-3"><span className="text-primary"><b>{this.state.selCustomerAddress}</b></span></div>
                        <div className="col-md-2"><span className="text-primary"><b>{this.state.selCustomerPhone}</b></span></div>
                        <div className="col-md-3"><span className="text-primary"><b>{this.state.selCustomerBalance} BDT</b></span></div>
                    </div>
                    <div className="user-cart mt-1">
                        <div className="card">
                            <table className="table table-striped">
                                <thead>
                                    <tr>
                                        <th>{translations["product_name"]}</th>
                                        <th>{translations["quantity"]}</th>
                                        <th className="text-right">
                                            {translations["price"]}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {cart.map((c) => (
                                        <tr key={c.id}>
                                            <td>{c.name}</td>
                                            <td>
                                                <input
                                                    type="text"
                                                    className="form-control form-control-sm qty product-input"
                                                    value={c.pivot.quantity}
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
                                {translations["checkout"]}
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
                    
                    <div className="order-product">
                        {products.map((p) => (
                            <div
                                onClick={() => this.addProductToCart(p.barcode)}
                                key={p.id}
                                className="item product-div"
                                id={'product-'+p.barcode}
                                style={{ width:'100px', overflow:'hidden',height:'140px' }}
                                title={p.name}
                            >
                                <img src={ p.image_url} alt="" />
                                <h5
                                    style={
                                        window.APP.warning_quantity > p.quantity
                                            ? { color: "red" }
                                            : {}
                                    }
                                >
                                    {p.name}({p.quantity})
                                </h5>
                            </div>
                        ))}
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
