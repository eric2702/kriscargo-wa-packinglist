<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { reactive } from "vue";
// import { Inertia } from "@inertiajs/inertia";
import { useForm } from "@inertiajs/vue3";

const form = useForm({
    file: null,
    from: "",
    to: "",
    saveType: "DOWNLOAD",
});

const handleFileChange = (e) => {
    form.file = e.target.files[0];
};

const handleSubmit = (e) => {
    e.preventDefault();
    //disable the upload button
    e.target[1].disabled = true;
    //make the color of the button gray
    e.target[1].classList.add("bg-gray-400");
    //on hover also gray
    e.target[1].classList.remove("hover:bg-blue-500");
    e.target[1].textContent = "Processing...";
    let formData = new FormData();
    formData.append("file", form.file);

    // axios
    //     .post(route("waBlastPackingList"), formData, {
    //         headers: {
    //             "Content-Type": "multipart/form-data",
    //         },
    //     })
    //     .then((response) => {
    //         //if saveType is DOWNLAOD, then download the file
    //         if (response.data.success) {
    //             console.log(response.data.message);
    //             Swal.fire({
    //                 icon: "success",
    //                 title: "Success",
    //                 text: response.data.message,
    //             });
    //         } else {
    //             Swal.fire({
    //                 icon: "error",
    //                 title: "Error",
    //                 text: response.data.message,
    //             });
    //         }
    //     })
    //     .catch((error) => {
    //         Swal.fire({
    //             icon: "error",
    //             title: "Error",
    //             text: error.response.data.message,
    //         });
    //     })
    //     .finally(() => {
    //         //enable the upload button
    //         e.target[1].disabled = false;
    //         //make the color of the button black
    //         e.target[1].classList.remove("bg-gray-400");
    //         //on hover also black
    //         e.target[1].classList.add("hover:bg-blue-500");
    //         e.target[1].textContent = "Upload";
    //     });

    axios
        .post(route("waBlastPackingList"), formData, {
            headers: {
                "Content-Type": "multipart/form-data",
            },
            responseType: "blob",
        })
        .then((response) => {
            // Create a blob URL for the response data
            const url = window.URL.createObjectURL(new Blob([response.data]));

            // Create a temporary anchor element
            const link = document.createElement("a");
            link.href = url;
            //get the filename from the response headers
            let filename = response.headers["content-disposition"]
                .split(";")[1]
                .split("=")[1];
            //remove the double quotes
            filename = filename.replace(/"/g, "");
            link.setAttribute("download", filename); // set the download attribute with the desired file name
            document.body.appendChild(link);

            // Trigger a click event on the anchor element to start the download
            link.click();

            // Remove the temporary anchor element
            document.body.removeChild(link);
        })
        .catch((error) => {
            Swal.fire({
                icon: "error",
                title: "Error",
                text: error.response.data.message,
            });
        })
        .finally(() => {
            //enable the upload button
            e.target[1].disabled = false;
            //make the color of the button black
            e.target[1].classList.remove("bg-gray-400");
            //on hover also black
            e.target[1].classList.add("hover:bg-blue-500");
            e.target[1].textContent = "Upload";
        });
};
</script>

<template>
    <AppLayout title="Upload">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Upload
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div
                    class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-4"
                >
                    <div class="flex">
                        <h1 class="text-3xl">Upload Container List</h1>
                        <button class="ml-auto">
                            <a
                                class="bg-green-500 text-white rounded py-2 px-4 hover:bg-blue-500"
                                href="/files/Format Upload Container List.csv"
                                ><i class="fa-solid fa-circle-down"></i>
                                Download Format
                            </a>
                        </button>
                    </div>

                    <form
                        @submit.prevent="handleSubmit"
                        class="max-w-md mx-auto mt-8"
                    >
                        <div class="mb-6">
                            <label
                                for="file"
                                class="block mb-2 uppercase font-bold text-xs text-gray-700"
                            >
                                File (.xls, .xlsx, .csv)
                            </label>
                            <!-- <input
                                type="file"
                                id="file"
                                name="file"
                                class="border border-gray-400 p-2 w-full"
                                accept=".csv"
                                required
                                @change="handleFileChange"
                            /> -->

                            <!-- accept xls, xlsx, and csv -->
                            <input
                                type="file"
                                id="file"
                                name="file"
                                class="border border-gray-400 p-2 w-full"
                                accept=".xls,.xlsx,.csv"
                                required
                                @change="handleFileChange"
                            />
                            <!-- <p class="text-sm text-gray-500">
                                <span style="color: red">*</span>Pastikan semua
                                kolom angka sudah diubah menjadi tipe Number
                            </p> -->
                        </div>

                        <button
                            type="submit"
                            class="bg-black text-white rounded py-2 px-4 hover:bg-blue-500"
                        >
                            Upload
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
