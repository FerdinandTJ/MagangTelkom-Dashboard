export default function HeadingSmall({
    title,
    description,
}: {
    title: string;
    description?: string;
}) {
    return (
        <header>
            <h3 className="mb-0.5 text-base font-medium text-gray-900 dark:text-white">{title}</h3>
            {description && (
                <p className="text-sm text-muted-foreground dark:text-gray-400">{description}</p>
            )}
        </header>
    );
}
