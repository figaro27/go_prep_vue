export default {
  async getMeal(mealId) {
    const { data } = await axios.get(`/api/me/meals/${mealId}`);
    return data;
  }
};
