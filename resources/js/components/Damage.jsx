import React, { Component } from "react";
import { createRoot } from "react-dom";
import axios from "axios";
import Swal from "sweetalert2";
import { isArray, sum } from "lodash";

class Damage extends Component {
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
        this.findOrderID = this.findOrderID.bind(this);
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
        axios.get("/admin/salesreturn/findorderid/0").then((res) => {
            const cart = res.data.salesreturn_item;
            const order = res.data.order;
            if(cart.length){
                const sub_total = this.getTotal(cart)
                const gr_total = sub_total - this.state.discount_amount
                const customer_id = order.customer_id
                const prev_balance = order.customer.balance
                const new_balance = order.customer.balance+gr_total
                const last_balance = order.customer.balance+gr_total
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
        const cart = this.state.cart.map((c) => {
            if (c.product_id === product_id) {
                c.qnty = qty;
                c_product = c
            }
            return c;
        });

        if (!qty) return;

        axios
            .post("/admin/salesreturn/changeqnty", { product_id, qnty: qty, sell_price: c_product.sell_price })
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
            const total = cart.map((c) => c.qnty * c.sell_price);
            return sum(total).toFixed(2);
        }
    }

    handleClickDelete(product_id) {
        console.log(product_id)
        axios
            .post("/admin/salesreturn/delete", { product_id, _method: "POST" })
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
        axios.post("/admin/cart/empty", { _method: "DELETE" }).then((res) => {
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

    addProductToCart(barcode) {
        if(!this.state.customer_id){
            Swal.fire('Please select a customer', 'warning');
            return false
        }
        const customer_id = this.state.customer_id
        let product = this.state.products.find((p) => p.id === barcode);
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
                .post("/admin/salesreturn/cart", { product_id:product.id, barcode, customer_id })
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

    findOrderID(event) {
        const order_id = event.target.value
        const key_code = event.keyCode

        if(order_id && key_code == 13){

            axios.get(`/admin/salesreturn/findorderid/${order_id}`).then((res) => {
                const order = res.data.order;
                const salesreturn_items = res.data.salesreturn_item;
                console.log('order',order);
                console.log('salesreturn_items',salesreturn_items);



                if(order){

                    const sub_total = this.getTotal(salesreturn_items)
                    const gr_total = sub_total - this.state.discount_amount
                    const customer_id = order.customer_id
                    const user_balance = order.customer.balance
                    const prev_balance = user_balance
                    const new_balance = user_balance - gr_total
                    const last_balance = new_balance
                    this.setState({ order_id, sub_total, gr_total,customer_id, prev_balance, new_balance,last_balance,
                        customer_info: order.customer,
                        cart: salesreturn_items,
                        selCustomerFName:order.customer.first_name,
                        selCustomerLName:order.customer.last_name,
                        selCustomerAddress:order.customer.address,
                        selCustomerPhone:order.customer.phone,
                        selCustomerBalance:order.customer.balance,
                     });
                }else{
                    const order_id = ""
                    const sub_total = 0
                    const gr_total = 0
                    const customer_id = ""
                    const prev_balance = 0
                    const new_balance = 0
                    const last_balance = 0
                    this.setState({ order_id,  cart:[], sub_total, gr_total,customer_id, prev_balance, new_balance,last_balance });
                }



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
            title: 'Save Sales Return',
            input: "text",
            inputValue: this.state.return_amount,
            showCancelButton: true,
            confirmButtonText: "Save Sale Return",
            cancelButtonText: this.state.translations["cancel_pay"],
            showLoaderOnConfirm: true,
            preConfirm: (amount) => {
                return axios
                    .post("/admin/salesreturn/finalsave", {
                        customer_id: this.state.customer_id,
                        order_id: this.state.order_id,
                        amount,
                    })
                    .then((res) => {
                        this.loadCart();
                        // Assuming the response includes an order ID or invoice URL
                        const printUrl = `/admin/orders/print/${res.data.id}`;
                        this.setState({ printUrl, return_amount:0 });
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

    changeReturnAmount = (e) =>{

        const new_balance = this.state.prev_balance
        const return_amount = e.target.value
        const last_balance = parseFloat(new_balance) + parseFloat(return_amount)

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
        const { cart, products, customers, barcode, translations } = this.state;
        console.log('cart:::', cart)
        return (
            <div className="row">

                <div className="col-md-6 col-lg-6">
                    <div className="row mb-2">
                        <div className="col-md-2">
                            <input type="text" onKeyUp={this.findOrderID} value={this.state.order_id} name="order-input" id="order-input" className="form-control border"></input>
                        </div>
                        <div className="col-md-4"><span className="text-danger"><b>{this.state.selCustomerFName } {this.state.selCustomerLName}</b></span></div>
                        <div className="col-md-2"><span className="text-danger"><b>{this.state.selCustomerAddress}</b></span></div>
                        <div className="col-md-2"><span className="text-danger"><b>{this.state.selCustomerPhone}</b></span></div>
                        <div className="col-md-2"><span className="text-danger"><b>{this.state.selCustomerBalance} BDT</b></span></div>
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
                                                    c.sell_price * c.qnty
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
                                onClick={() => this.addProductToCart(p.id)}
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

export default Damage;

const root = document.getElementById("damage-cart");
if (root) {
    const rootInstance = createRoot(root);
    rootInstance.render(<Damage />);
}
