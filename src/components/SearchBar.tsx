import { SearchIcon } from "lucide-react";

export function SearchBar({ defaultValue = "" }: { defaultValue?: string }) {
  return (
    <form action="/search" method="GET" className="relative max-w-lg mx-auto">
      <SearchIcon className="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" size={20} />
      <input
        type="search"
        name="q"
        defaultValue={defaultValue}
        placeholder="Search brands, categories..."
        className="w-full pl-10 pr-4 py-3 rounded-full border-2 border-gray-200 focus:border-teal-500 focus:outline-none text-gray-900"
        aria-label="Search"
      />
    </form>
  );
}
