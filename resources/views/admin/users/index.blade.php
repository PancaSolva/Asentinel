@extends('layouts.admin')

@section('title', 'Users Management')

@section('content')
<div x-data="userManagement()" x-init="fetchUsers()" class="space-y-6">
    <div class="flex justify-between items-center">
        <h2 class="text-3xl font-bold text-gray-900 dark:text-white">Users</h2>
        <button @click="openCreateModal()" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg font-semibold">
            Tambah User
        </button>
    </div>

    <!-- User Table -->
    <div class="bg-white dark:bg-gray-800 shadow overflow-hidden rounded-lg">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Nama</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Role</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                <template x-for="user in users" :key="user.id">
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900 dark:text-white" x-text="user.name"></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900 dark:text-white" x-text="user.email"></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full"
                                  :class="user.role === 'admin' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-100' : 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-100'"
                                  x-text="user.role.charAt(0).toUpperCase() + user.role.slice(1)">
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                            <button @click="openEditModal(user)" class="text-yellow-600 hover:text-yellow-900">Edit</button>
                            <button @click="deleteUser(user.id)" class="text-red-600 hover:text-red-900">Hapus</button>
                        </td>
                    </tr>
                </template>
                <tr x-show="users.length === 0">
                    <td colspan="4" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                        Belum ada user. <button @click="openCreateModal()" class="font-semibold hover:text-blue-500">Tambah sekarang</button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Modal for Create/Edit -->
    <div x-show="isModalOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form @submit.prevent="saveUser">
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" x-text="editMode ? 'Edit User' : 'Tambah User'"></h3>
                        <div class="mt-4 space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama</label>
                                <input type="text" x-model="formData.name" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                                <input type="email" x-model="formData.email" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div x-show="!editMode">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password</label>
                                <input type="password" x-model="formData.password" :required="!editMode" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Role</label>
                                <select x-model="formData.role" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="user">User</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Simpan
                        </button>
                        <button @click="isModalOpen = false" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function userManagement() {
        return {
            users: [],
            isModalOpen: false,
            editMode: false,
            formData: {
                id: null,
                name: '',
                email: '',
                password: '',
                role: 'user'
            },
            fetchUsers() {
                fetch('{{ route("admin.api.users.index") }}', {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        this.users = data.data;
                    }
                });
            },
            openCreateModal() {
                this.editMode = false;
                this.formData = { id: null, name: '', email: '', password: '', role: 'user' };
                this.isModalOpen = true;
            },
            openEditModal(user) {
                this.editMode = true;
                this.formData = { ...user };
                this.isModalOpen = true;
            },
            saveUser() {
                const method = this.editMode ? 'PUT' : 'POST';
                const url = this.editMode 
                    ? `{{ url('admin/api/users') }}/${this.formData.id}`
                    : '{{ route("admin.api.users.store") }}';

                fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(this.formData)
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        this.isModalOpen = false;
                        this.fetchUsers();
                        alert(data.message);
                    } else {
                        alert(Object.values(data.errors).flat().join('\n'));
                    }
                });
            },
            deleteUser(id) {
                if (!confirm('Hapus user ini?')) return;

                fetch(`{{ url('admin/api/users') }}/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        this.fetchUsers();
                        alert(data.message);
                    }
                });
            }
        }
    }
</script>
@endsection
