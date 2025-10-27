export default function AppLogo() {
    return (
        <>
            <div className="flex aspect-square size-8 items-center justify-center rounded-md bg-white dark:bg-gray-800 shadow-sm border-2 border-gray-200 dark:border-gray-700 p-1">
                <img 
                    src="/telkom-logo.svg" 
                    alt="Telkom Logo" 
                    className="size-5"
                />
            </div>
            <div className="ml-2 grid flex-1 text-left text-sm">
                <span className="mb-0.5 truncate leading-tight font-semibold text-gray-900 dark:text-gray-100">
                    Dashboard
                </span>
            </div>
        </>
    );
}
