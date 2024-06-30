<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { reactive } from "vue";
// import { Inertia } from "@inertiajs/inertia";
import { useForm } from "@inertiajs/vue3";
import axios from "axios";

const form = useForm({
    file: null,
    trip_no: "",
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
    formData.append("trip_no", form.trip_no);

    axios
        .post(route("lclWaBlastPackingList"), formData, {
            headers: {
                "Content-Type": "multipart/form-data",
            },
            responseType: "blob",
        })
        .then(async (response) => {
            // Check if the response is JSON
            const contentType = response.headers["content-type"];
            if (contentType && contentType.includes("application/json")) {
                const responseData = await response.data.text();
                const responseJson = JSON.parse(responseData);
                console.log(responseJson.success);
                console.log(responseJson);
                if (!responseJson.success) {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: responseJson.message,
                    });
                    e.target[0].value = "";

                    return;
                }
            } else {
                // If response is not JSON, proceed to download
                const url = window.URL.createObjectURL(
                    new Blob([response.data])
                );
                const link = document.createElement("a");
                link.href = url;
                let filename = response.headers["content-disposition"]
                    .split(";")[1]
                    .split("=")[1]
                    .replace(/"/g, "");
                link.setAttribute("download", filename);
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);

                //remove the file from the input
                e.target[0].value = "";
            }
        })
        .catch((error) => {
            console.log(error);
            let message = "An error occurred";
            Swal.fire({
                icon: "error",
                title: "Error",
                text: message,
            });

            e.target[0].value = "";
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
    <AppLayout title="LCL">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                LCL
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div
                    class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-4"
                >
                    <div class="flex">
                        <h1 class="text-3xl">LCL Container List</h1>
                        <button class="ml-auto">
                            <a
                                class="bg-green-500 text-white rounded py-2 px-4 hover:bg-blue-500"
                                href="/files/Format Upload Container List (LCL).csv"
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
                            <!-- <label
                                for="file"
                                class="block mb-2 uppercase font-bold text-xs text-gray-700"
                            >
                                File (.xls, .xlsx, .csv)
                            </label> -->
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
                            <!-- <input
                                type="file"
                                id="file"
                                name="file"
                                class="border border-gray-400 p-2 w-full"
                                accept=".xls,.xlsx,.csv"
                                required
                                @change="handleFileChange"
                            /> -->
                            <!-- <p class="text-sm text-gray-500">
                                <span style="color: red">*</span>Pastikan semua
                                kolom angka sudah diubah menjadi tipe Number
                            </p> -->

                            <label
                                for="trip_no"
                                class="block uppercase font-bold text-xs text-gray-700"
                            >
                                Trip No
                            </label>

                            <input
                                type="text"
                                name="trip_no"
                                id="trip_no"
                                class="border border-gray-400 p-2 w-full mt-2"
                                placeholder="Trip No"
                                required
                                v-model="form.trip_no"
                            />
                        </div>

                        <button
                            type="submit"
                            class="bg-black text-white rounded py-2 px-4 hover:bg-blue-500"
                        >
                            Submit
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
