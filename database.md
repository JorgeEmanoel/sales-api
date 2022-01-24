users
    id
    username
    email
    password

providers
    id
    name
    document
    document_type
    status
    shared
    user_id

products
    id
    name
    quantity
    price
    user_id

clients
    id
    name
    phone
    email
    user_id

sales
    id
    total
    payment_method
    status[placed,paid,cancelled]
    client_id

sale_products
    id
    total
    quantity
    paid_unit_price
    sale_id
    product_id
