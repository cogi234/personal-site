<button {{ $attributes->merge(['class' => 'h-10 my-auto mx-auto inline-flex items-center px-4 py-2 rounded-md font-bold text-xs uppercase bg-gray-800 dark:bg-gray-200 border border-transparent text-white dark:text-gray-800 tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>