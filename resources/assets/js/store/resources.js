import { resource, createResource } from "vuex-pagination";

const fetchOrders = async ({ page, pageSize, args }) => {
  const res = await axios.get(`/api/me/orders/${page}/${pageSize}`, {
    params: args
  });
  const { data } = res;
  const { total } = data;
  let orders = data.data || [];

  if (_.isArray(orders)) {
    orders = _.map(orders, order => {
      order.created_at = moment.utc(order.created_at).local();
      order.updated_at = moment.utc(order.updated_at).local();
      order.delivery_date = moment.utc(order.delivery_date);
      return order;
    });

    console.log("ORDERS", orders);

    return {
      total,
      data: orders
    };
  } else {
    return {
      total: 0,
      data: []
    };
  }
};

const orderResource = createResource("orders", fetchOrders, { prefetch: true });

export { orderResource };
