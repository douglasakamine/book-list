<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header text-center">
                        <h2>Book List</h2>
                    </div>
                    <div class="card-body">
                        <b-form class="mb-5">
                            <b-form-group
                                id="input-group-1"
                                label="Title:"
                                label-for="input-1"
                            >
                                <b-form-input
                                id="input-1"
                                v-model="form.title"
                                type="text"
                                placeholder="Enter book's title"
                                required
                                ></b-form-input>
                            </b-form-group>

                            <b-form-group
                                id="input-group-2"
                                label="Author:"
                                label-for="input-2"
                            >
                                <b-form-input
                                id="input-2"
                                v-model="form.author"
                                placeholder="Enter author's name"
                                required
                                ></b-form-input>
                            </b-form-group>
                            <b-button @click="addBook()" variant="primary">Add</b-button>
                        </b-form>

                        <b-row align-h="end">
                            <b-col cols="6">
                                <b-input-group
                                    class="mb-3"
                                >
                                    <b-form-input></b-form-input>
                                    <b-input-group-append>
                                    <b-button size="sm" text="Button" variant="success">Search</b-button>
                                    </b-input-group-append>
                                </b-input-group>
                            </b-col>
                        </b-row>
                        

                        <b-table 
                            striped 
                            hover 
                            :items="items" 
                            :fields="fields"
                        >
                            <template
                                slot="cell(delete_field)"
                                slot-scope="items"    
                            >
                                <b-button variant="danger" @click="deleteBook(items.id)">Delete</b-button>
                            </template>
                            <template
                                slot="cell(export_field)"
                                slot-scope="items"    
                            >
                                <b-button variant="success" @click="exportBook(items.id)">Export</b-button>
                            </template>
                        </b-table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                items: [],
                form: {
                    title: "",
                    author: ""
                },
                fields: [
                    {
                        key: 'title',
                        sortable: true
                    },
                    {
                        key: 'author',
                        sortable: true
                    },
                    {
                        key: 'delete_field',
                        sortable: false
                    },
                    {
                        key: 'export_field',
                        sortable: false
                    }
                ],
            }
        },
        methods: {
            getBooks() {
                axios.get('api/book/get').then((response) => {
                    this.items = response.data;
                });
            
            },
            addBook() {
                axios.post('api/book/save').then((response) => {
                    console.log(response)
                });
            },
            deleteBook() {

            }
        },
        created() {
            this.getBooks();
        }
    }
</script>
