<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card mb-3">
                    <div class="card-header text-center">
                        <h2>Book List</h2>
                    </div>
                    <div class="card-body" ref="form">
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
                                <b-button
                                    v-if="editMode"
                                    @click="cancelEdition()" 
                                    variant="secondary"
                                >
                                    Cancel
                                </b-button>
                                <b-button
                                    v-if="editMode"
                                    @click="editBook()" 
                                    variant="primary"
                                >
                                    Save
                                </b-button>
                                <b-button
                                    v-else
                                    @click="addBook()" 
                                    variant="success"
                                >
                                    Add
                                </b-button>
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
                                    <b-form-input
                                        placeholder="Search by a title or author..."
                                        @keypress="search()" 
                                        v-model="searchCriteria"
                                    ></b-form-input>
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
                            class="books-table"
                            striped 
                            hover 
                            :items="items" 
                            :fields="fields"
                            fixed
                            show-empty
                        >   
                            <template
                                slot="cell(title)"   
                                slot-scope="items"
                            >
                            <span :id="`title-id-${items.item.id}`">{{ items.item.title }}</span>
                            <b-popover
                                :target="`title-id-${items.item.id}`"
                                placement="bottomright"
                                triggers="hover focus"
                                :content="items.item.title"
                            ></b-popover>
                            </template>
                            <template
                                slot="cell(author)"   
                                slot-scope="items"
                            >
                            <span :id="`author-id-${items.item.id}`">{{ items.item.author }}</span>
                            <b-popover
                                :target="`author-id-${items.item.id}`"
                                placement="bottomright"
                                triggers="hover focus"
                                :content="items.item.author"
                            ></b-popover>
                            </template>
                            <template
                                slot="cell(buttons)"   
                                slot-scope="items"
                            >
                                <b-button variant="info"
                                    @click="loadBookToBeEdited(items.index)"
                                >
                                    <b-icon icon="pencil-square"></b-icon>
                                </b-button>
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

        <b-overlay v-if="loading"
            show
            :opacity="0.5"
            rounded="lg"
            fixed
            no-wrap
        ></b-overlay>
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
                        class: 'title-col',
                        sortable: true
                    },
                    {
                        key: 'author',
                        class: 'author-col',
                        sortable: true
                    },
                    {
                        key: 'buttons',
                        class: 'buttons',
                        sortable: false,
                        label: ''
                    }
                ],
                deleteTarget: {},
                deleteModalText: "Are you sure to delete this book?",
                selectedColumns: ["title", "author"],
                exportColumns: [
                    { text: "Title", value: "title"},
                    { text: "Author", value: "author"}
                ],
                editMode: false,
                loading: false
            }
        },
        methods: {
            getBooks() {
                this.loading = true;
                axios.get(`api/book/get?searchCriteria=${this.searchCriteria}`).then((response) => {
                    this.items = response.data;
                    this.loading = false;
                });
            },
            search() {
                this.getBooks();
            },  
            addBook() {
                this.loading = true;
                axios.post('api/book/save', this.form)
                .then((response) => {
                    this.successAlert = true;
                    this.form = this.getInitialForm();
                    this.getBooks();
                    this.loading = false;
                    Vue.toasted.success('Book successfully added');
                }),(error) => {
                    console.log(error);
                    this.loading = false;
                    Vue.toasted.error('Error while adding');
                };
            },
            loadBookToBeEdited(index) {
                this.editMode = true;
                this.form = {...this.items[index]};
                let el = this.$refs.form;
                el.scrollIntoView({ behavior: 'smooth'});
            },
            editBook() {
                this.loading = true;
                axios.patch('api/book/edit', this.form)
                .then((response) => {
                    this.editMode = false;
                    this.form = this.getInitialForm();
                    this.getBooks();
                    this.loading = false;
                    Vue.toasted.success('Book successfully edited');
                }),(error) => {
                    console.log(error);
                    this.loading = false;
                    Vue.toasted.error('Error while editing');
                };
            },
            deleteBook() {
                this.loading = true;
                axios.delete(`api/book/delete/${this.deleteTarget.id}`)
                .then((response) => {
                    this.getBooks();
                    this.loading = false;
                    Vue.toasted.info('Book Deleted');
                }),(error) => {
                    console.log(error);
                    this.loading = false;
                    Vue.toasted.error('Error while deleting');
                };
            },
            cancelEdition() {
                this.editMode = false;
                this.form = this.getInitialForm();
            },
            exportBooksToCsv() {
                this.loading = true;
                const CSV = "csv";
                let exportInfo = this.serializeExportInfo();
                axios.post('api/book/exportcsv', exportInfo).then((response) => {
                    this.loading = false;
                    this.downloadFile(response.data, CSV);
                });
            },
            exportBooksToXml() {
                this.loading = true;
                const XML = "xml";
                let exportInfo = this.serializeExportInfo();
                axios.post('api/book/exportxml', exportInfo).then((response) => {
                    this.loading = false;
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

<style scoped>

.books-table >>> .buttons {
    width: 20%;
}
.books-table >>> .title-col, .books-table >>> .author-col {
    overflow-x: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
</style>
