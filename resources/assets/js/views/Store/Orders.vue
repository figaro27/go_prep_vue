<template>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <Spinner v-if="isLoading"/>
                        <v-client-table :columns="columns" :data="tableData" :options="options" v-show="!isLoading">
                            <div slot="actions" class="text-nowrap" slot-scope="props">
                                <button class="btn btn-primary btn-sm" @click="">Mark As Delivered</button>
                            </div>


                            <div slot="child_row" slot-scope="props">
                                {{ tableData[props.row.id].user.email }}
                            </div>


                        </v-client-table>
                    </div>
                </div>
            </div>
        </div>
</template>

<style>
th:nth-child(3) {
  text-align: center;
}

.VueTables__child-row-toggler {
  width: 16px;
  height: 16px;
  line-height: 16px;
  display: block;
  margin: auto;
  text-align: center;
}

.VueTables__child-row-toggler--closed::before {
  content: "+";
}

.VueTables__child-row-toggler--open::before {
  content: "-";
}
</style>


<script>
import Spinner from "../../components/Spinner";
    export default {
        components: {
            Spinner
        },
        data(){
            return {
                isLoading: true,
                columns: [
                    "id",
                    "user.email",
                    "amount",
                    "created_at",
                    "actions"
                ],
                tableData: [],
            options: {
                headings: {
                  id: "Order #",
                  "order.user.emai": "Email",
                  amount: "Total",
                  created_at: "Order Placed",
                  actions: "Actions"
                }
            },
        }
    },
        mounted()
        {
            this.getTableData();
        },
        methods: {
            getTableData() {
              let self = this;
              axios.get("/api/me/orders")
                .then(function(response){    
                self.tableData = response.data;
                self.isLoading = false;
              });
            },
        }
    }
</script>