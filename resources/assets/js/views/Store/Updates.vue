<template>
  <div>
    <div class="row mt-3">
      <div class="col-md-8 offset-md-2">
        <div class="card">
          <div class="card-body">
            <h3 class="center-text mb-5">New Updates 10/21/19</h3>
            <ul class="updates">
              <li>
                <p class="strong">Text Based Menus</p>
                <p>
                  -We've added a different type of menu option that emphasizes
                  the meal's description more than pictures so you don't have to
                  always feel the need to take pictures of your meals. This new
                  text based menu can still show images, but they are smaller
                  than the regular image-based menu.
                </p>
                <p>
                  -Here is an example of a text-based menu. You can view the
                  meal in the top right to see an example of a meal without an
                  image:
                  <a
                    href="https://gyazo.com/148d1694cc72341111ed7b207ef93742"
                    target="_blank"
                    >View Image</a
                  >
                  You can change your menu style at the bottom of the Menu tab
                  of the Settings page found here:
                  <a
                    href="https://gyazo.com/f8ea3c2c5f74617a718a93b1a2857f2e"
                    target="_blank"
                    >View Image</a
                  >
                </p>
              </li>
              <li>
                <p class="strong">Nutrition / Ingredients Improvements</p>
                <p>
                  -You can now set "Servings Per Container" and "Serving Size
                  Unit" for each meal. This updates the nutrition facts to show
                  how many servings are included in the meal and divides the
                  nutrition facts amounts by that number. For most meals, you
                  can leave this number at 1.
                </p>
                <p>
                  -Nutrition facts now change on your menu if your customer
                  selects different meal variations and these variations have
                  different ingredients or amounts.
                </p>
                <p>
                  -Macros are now shown underneath each individual ingredient
                  you add to your meal for your convenience.
                  <a
                    href="https://gyazo.com/d6d5593b00db900c9c6a4c0152eb2b13"
                    target="_blank"
                    >View Image</a
                  >
                </p>
                <p>
                  -The ingredients at the bottom of nutrition facts are now
                  listed in descending order of weight to show the most
                  prominent ingredients first.
                </p>
              </li>
              <li>
                <p class="strong">Refunds / Charges / Voids</p>
                <p>
                  -You can now refund your customer and charge your customer
                  directly through the platform. These are all found on the View
                  Order modal (popup) which will eventually be moved to their
                  own pages for more surface area.
                  <a
                    href="https://gyazo.com/df68662aa3a21ee8fa03f412dc3a9792"
                    target="_blank"
                    >View Image</a
                  >
                  You can only refund up to the original amount paid plus any
                  additional charges. The order amount breakdown will indicate
                  any amount refunded or charged to both you and your customer.
                </p>
                <p>
                  -We've also added the ability to Void your orders. This still
                  shows the order in your Orders table with an indication that
                  it's been voided, but it completely removes the order and all
                  of the meal information from all of your reports. You can
                  unvoid the order after you void it if you have to.
                </p>
              </li>
              <li>
                <p class="strong">Cash On Delivery Updates</p>
                <p>
                  -We've made cash on delivery orders show a balance by default.
                  This means if you or your customer choose cash on delivery as
                  the option, it will show a balance for the full amount of the
                  order in their notification emails and packing slips in order
                  to provide a better indication that money is owed on that
                  order. If you'd like us to turn this off for you and mark all
                  cash orders as paid as soon as the order is created, please
                  contact me:
                  <a href="mailto:mike@goprep.com">mike@goprep.com</a>
                </p>
              </li>
              <li>
                <p class="strong">Packing Slips</p>
                <p>
                  -We've made improvements to the visual design of the packing
                  slips and included more relevant details like the pricing
                  breakdown. We still plan to improve the design of the packing
                  slips over time.
                </p>
              </li>
              <li>
                <p class="strong">Add Customer</p>
                <p>
                  -You can now check if your customers have already registered
                  an account and add them to your customer list. We've added a
                  field at the top of the Add Customer popup that let's you
                  check. If they have an account, you'll be prompted to press a
                  button to add them as a customer. We've also added an option
                  to check a box that generates a random email for the new user
                  if you happen to not have their email.
                  <a
                    href="https://gyazo.com/e31d60bf3cdd35999a0ee01371a9790a"
                    target="_blank"
                    >View Image</a
                  >
                </p>
                <p>
                  -In the near future, you'll be able to edit your customer
                  information including email addresses. If you add a customer,
                  and that customer registers a separate account with a
                  different email and places an order, you'll also be able to
                  merge the two accounts together into one customer to prevent
                  any duplicates.
                </p>
              </li>
              <li>
                <p class="strong">Various Improvements / Bug Fixes</p>
                <p>
                  -Some issues involving manual orders especially with adding
                  separate credit cards have been fixed. As of now, if you have
                  to refresh the page, please go back to Orders and create the
                  manual order again. This is a known issue that we are working
                  on. We also plan on making manual orders work on tablets and
                  phones soon enough however we recommended doing everything
                  from a computer.
                </p>
                <p>
                  -PDF payments report has been fixed. We plan on making massive
                  improvements to the payments reporting over time.
                </p>
                <p>
                  -Added a noticeable flash message on the checkout page for
                  your subscribed customers that tells them they currently have
                  an active subscription with you in order to prevent double
                  ordering. Some customers unfortunately misunderstand what a
                  weekly subscription is or forget they have one and sometimes
                  place a 2nd order even if they've already been charged for
                  their subscription that week. We send an email notification to
                  them 24 hours before their subscription renews giving them
                  notice that their card is going to get charged and they can
                  cancel or update their meals if they wish. You can also
                  optionally turn off subscriptions in Settings.
                  <a
                    href="https://gyazo.com/3789d0ec338192090ac22f6c2cc86107"
                    target="_blank"
                    >View Image</a
                  >
                </p>
                <p>
                  -Pausing and resuming subscriptions has been removed for the
                  time being until further notice as we have experienced some
                  timing issues.
                </p>
                <p>
                  -Fixed a bug involving ingredients not saving for meal
                  variations if you press the Save button in the Ingredients
                  tab.
                </p>
                <p>
                  -Auto-populating your state in the drop down box for your
                  customers when they create an account.
                </p>
                <p>
                  -Fixed the issue of having meals stay in customer's bags over
                  time even if you deactivate or delete those meals from your
                  menu. Any meals that a customer adds to their bag now stays
                  there unless you deactivate or delete any meals on your menu.
                  This then clears out the bags of all of your customers in
                  order prevent them ordering a meal you aren't offering that
                  week.
                </p>
                <p>
                  -We are now forming a weekly flow to collect any feedback,
                  bugs, and improvements and push it live once a week usually
                  Saturday or Sunday as these are slow order days for most
                  companies. We'll continue using this area to inform you about
                  weekly or monthly updates. As with any application still in
                  development with new features being added, there may be an
                  occasional bug you or your customers experience. We
                  extensively test everything before we make it live, but
                  sometimes we don't catch everything. If you happen to find any
                  bug or anything out of place, please email me at
                  <a href="mailto:mike@goprep.com">mike@goprep.com</a> and I'll
                  fix it as fast as possible depending on the urgency. Many new
                  features and improvements are coming in the following weeks
                  and months. Thank you for being an awesome GoPrep customer.
                </p>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<style>
.updates li {
  margin-bottom: 60px;
}
.updates li p {
  margin-bottom: 20px;
}

.updates li p a {
  font-weight: bold;
}
</style>
<script>
export default {
  data() {
    return {};
  },

  mounted() {
    axios.get("/api/me/viewedUpdates").then(resp => {
      router.push("store-my-account");
    });
  },
  methods: {}
};
</script>
