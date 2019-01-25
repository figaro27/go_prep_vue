let cards = {};

const files = require.context('payment-icons/min/flat', true, /\.svg$/i)
files
  .keys()
  .map(key => cards[key.split('/').pop().split('.')[0]] = files(key))

export {
  cards,
}