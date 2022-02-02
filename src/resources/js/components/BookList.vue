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
                                <b-button v-b-modal.export-modal>Export to file</b-button>
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

            @ok="deleteBook()"
        >
            <p class="my-4">{{ deleteModalText }}</p>
        </b-modal>

        <b-modal
            id="export-modal" 
            title="Export to File"
            header-bg-variant="primary"
            centered
        >
            <b-form-group
                label="What informations would you like to export?"
            >
            <b-form-checkbox-group
                v-model="selectedColumns"
                :options="exportColumns"
            ></b-form-checkbox-group>
            </b-form-group>

            <template slot="modal-footer">
                <b-button
                    class="mr-auto"
                    variant="secondary"
                    bv::hide::modal
                >
                    Cancel
                </b-button>
                <b-button
                    variant="info"
                    @click="exportBooksToCsv()"
                >
                    CSV
                </b-button>
                <b-button
                    variant="primary"
                    @click="exportBooksToXml()"
                >
                    XML
                </b-button>
            </template>
        </b-modal>
    </div>
</template>

<script>
    const CSV = "csv";
    const XML = "xml";

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
                deleteModalText: "Are you sure to delete this book?",
                selectedColumns: ["title", "author"],
                exportColumns: [
                    { text: "Title", value: "title"},
                    { text: "Author", value: "author"}
                ]
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
            exportBooksToCsv() {
                const CSV = "csv";
                let exportInfo = this.serializeExportInfo();
                axios.post('api/book/exportcsv', exportInfo).then((response) => {
                    this.downloadFile(response.data, CSV);
                });
            },
            exportBooksToXml() {
                const XML = "xml";
                let exportInfo = this.serializeExportInfo();
                axios.post('api/book/exportxml', exportInfo).then((response) => {
                    this.downloadFile(response.data, XML);
                });
            },
            serializeExportInfo() {
                let exportInfo = {
                    title: false,
                    author: false
                };
                if(this.selectedColumns.includes("title")) exportInfo.title = true;
                if(this.selectedColumns.includes("author")) exportInfo.author = true;

                return exportInfo;
            },
            downloadFile(file, type) {
                let blob = new Blob([file], { type: `application/${type}` });
                let link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                link.download = `books.${type}`;
                link.click();
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
