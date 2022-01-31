<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card mb-3">
                    <div class="card-header text-center">
                        <h2>Book List</h2>
                    </div>
                    <div class="card-body">
                        <b-form-group
                            id="input-group-1"
                            label="Title:"
                            label-cols="4" 
                            label-cols-lg="2"
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
                            label-cols="4" 
                            label-cols-lg="2"
                            label-for="input-2"
                        >
                            <b-form-input
                            id="input-2"
                            v-model="form.author"
                            placeholder="Enter author's name"
                            required
                            ></b-form-input>
                        </b-form-group>
                        <b-row align-h="end">
                            <b-col cols="6" class="text-right">
                                <b-button @click="addBook()" variant="success">Add</b-button>
                            </b-col>  
                        </b-row>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <b-row align-h="end">
                            <b-col cols="6">
                                <b-dropdown id="dropdown-1" text="Export data" class="mb-3">
                                    <b-dropdown-item  @click="exportBooks('CSV')">CSV</b-dropdown-item>
                                    <b-dropdown-item  @click="exportBooks('XML')">XML</b-dropdown-item>
                                </b-dropdown>
                            </b-col>
                            <b-col cols="6">
                                <b-input-group
                                    class="mb-3"
                                >
                                    <b-form-input v-model="searchCriteria"></b-form-input>
                                    <b-input-group-append>
                                        <b-button 
                                            size="sm" 
                                            text="Button" 
                                            variant="primary"
                                            @click="search()"
                                        >
                                        Search
                                        </b-button>
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
                                slot="cell(delete)"   
                                slot-scope="items"
                            >
                                <b-button variant="danger"
                                    v-b-modal.delete-modal
                                    @click="deleteTarget = items.item"
                                >
                                    <b-icon icon="trash"></b-icon>
                                </b-button>
                            </template>
                        </b-table>
                    </div>
                </div>
            </div>
        </div>
        <b-modal
            id="delete-modal" 
            title="Delete Book" 
            centered
            header-bg-variant="danger"
            header-text-variant="light"
            ok-title="Yes"
            ok-variant="danger"
            cancel-title="No"

            @ok="deleteBook()">
                <p class="my-4">{{ deleteModalText }}</p>
        </b-modal>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                items: [],
                form: this.getInitialForm(),
                searchCriteria: "",
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
                        key: 'delete',
                        class: 'w-10',
                        sortable: false
                    }
                ],
                deleteTarget: {},
                deleteModalText: "Are you sure to delete this book?"
            }
        },
        methods: {
            getBooks() {
                axios.get(`api/book/get?searchCriteria=${this.searchCriteria}`).then((response) => {
                    this.items = response.data;
                });
            },
            search() {
                this.getBooks();
            },  
            addBook() {
                axios.post('api/book/save', this.form)
                .then((response) => {
                    this.form = this.getInitialForm();
                    this.getBooks();
                });
            },
            deleteBook() {
                axios.delete(`api/book/delete/${this.deleteTarget.id}`)
                .then((response) => {
                    this.getBooks();
                });
            },
            exportBooks(type) {
                axios.get(`api/book/export?type=${type}`).then((response) => {
                    let blob = new Blob([response.data], { type: 'application/csv' })
                    let link = document.createElement('a')
                    link.href = window.URL.createObjectURL(blob)
                    link.download = 'test.csv'
                    link.click()
                });
            },
            getInitialForm() {
                return {
                    title: "",
                    author: ""
                }
            }
        },
        created() {
            this.getBooks();
        }
    }
</script>
