<x-filament::page>
    <div class="space-y-6">
        <h2 class="text-2xl font-bold">User Profile</h2>

        <div class="grid grid-cols-1 md:grid-cols-2" style="gap:1rem 2rem">
            <div>
                <strong>Name :</strong> 
                <input type="text" value="{{ $userData['name'] }}" class="block w-full transition duration-75 border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-white"
                readonly>
            </div>
            <div>
                <strong>Email:</strong>
                <input type="text" value="{{ $userData['email'] }}" class="block w-full transition duration-75 border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-white"
                readonly>
            </div>
            <div>
                <strong>Telephone:</strong>
                <input type="text" value="{{ $userData['telephone'] }}" class="block w-full transition duration-75 border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-white"
                readonly>
            </div>
            <div>
                <strong>Status:</strong>
                <input type="text" value="{{ $userData['status'] }}" class="block w-full transition duration-75 border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-white"
                readonly>
            </div>
            <div>
                <strong>Gang:</strong>
                <input type="text" value="{{ $userData['gang'] }}" class="block w-full transition duration-75 border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-white"
                readonly>
            </div>
            <div>
                <strong>Role:</strong>
                <input type="text" value="{{ $userData['role'] }}" class="block w-full transition duration-75 border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-white"
                readonly>
            </div>
        </div>
    </div>
</x-filament::page>
