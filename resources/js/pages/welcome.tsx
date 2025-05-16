import React, { useState, useRef, useEffect } from 'react'
import axios from 'axios'
import { Head, Link } from '@inertiajs/react'
import { route } from 'ziggy-js'
import { SimpleAppHeader } from '@/components/simple-app-header'
import { PlaceholderPattern } from '@/components/ui/placeholder-pattern'
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card'
import { AspectRatio } from '@/components/ui/aspect-ratio'
import { Button } from '@/components/ui/button'
import { Command, CommandInput } from '@/components/ui/command'
import Pagination from '@/components/pagination'

type Post = {
    id: number
    slug: string
    title: string
    description: string
    feature_image: string
}

type Meta = {
    current_page: number
    last_page: number
    [key: string]: any
}

type Props = {
    auth: any
    posts: {
        data: Post[]
        meta: Meta
    }
}

type PaginationHandle = {
    handleClickNextPage: () => void
    handleClickPrevPage: () => void
    canPreviousPage: boolean
    canNextPage: boolean
}

const Welcome: React.FC<Props> = ({ auth, posts: initialPosts }) => {
    const [query, setQuery] = useState('')
    const [posts, setPosts] = useState<Post[]>(initialPosts?.data ?? [])
    const [meta, setMeta] = useState<Meta>(initialPosts?.meta ?? { current_page: 1, last_page: 1 })
    const paginationRef = useRef<PaginationHandle>(null)

    const handleSearch = async () => {
        try {
            const queryParams = new URLSearchParams({ q: query }).toString()
            const url = route('home.blog.search') + `?${queryParams}`

            const response = await axios.get(url)
            if (response.status === 200) {
                setPosts(response.data.data)
                setMeta(response.data.meta)
            }
        } catch (error) {
            console.error(error)
        }
    }

    return (
        <>
            <Head title="Home" />
            <SimpleAppHeader
                showNav={true}
                authenticated={auth}
            />
            <section className="flex items-center justify-center h-80 bg-gray-100 text-black">
                <div className="text-center">
                    <h1 className="text-5xl font-bold sm:text-6xl">Bloggers</h1>
                    <p className="mt-4 text-gray-600">All latest blogs</p>
                    <div className="flex items-center justify-end p-4">
                        <Command className="rounded-lg border shadow-md max-w-sm mr-4 flex flex-row">
                            <CommandInput
                                placeholder="Search"
                                className="w-full text-md"
                                value={query}
                                onValueChange={setQuery}
                            />
                        </Command>
                        <Button onClick={handleSearch}>Search</Button>
                    </div>
                </div>
            </section>

            <main className="flex-grow py-12 bg-white dark:bg-gray-950">
                <div className="max-w-7xl mx-auto px-4">
                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        {posts.map((card) => (
                            <Link key={card.id} href={route('post.show', card.slug)}>
                                <Card className="group overflow-hidden rounded-lg shadow-md transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
                                    <CardHeader className="!aspect-[2/1] w-full p-0 relative aspect-video overflow-hidden">
                                        <AspectRatio ratio={16 / 9}>
                                            <img
                                                src={card.feature_image}
                                                alt={card.title}
                                                className="w-full h-full object-contain"
                                            />                                        </AspectRatio>
                                        <PlaceholderPattern className="w-full h-full" />
                                    </CardHeader>
                                    <CardContent className="p-4">
                                        <CardTitle className="text-xl font-bold transition group-hover:text-red-600 sm:text-2xl">
                                            {card.title}
                                        </CardTitle>
                                        <CardDescription className="mt-3 max-w-2xl text-gray-600 leading-none truncate">
                                            {card.description}
                                        </CardDescription>
                                    </CardContent>
                                </Card>
                            </Link>
                        ))}
                    </div>

                    <Pagination meta={meta} ref={paginationRef}>
                        <div className="mt-20">
                            <nav role="navigation" aria-label="Pagination Navigation" className="flex justify-between">
                                <Button
                                    onClick={() => paginationRef.current?.handleClickPrevPage()}
                                    disabled={!paginationRef.current?.canPreviousPage}
                                >
                                    ← Previous
                                </Button>
                                <Button
                                    onClick={() => paginationRef.current?.handleClickNextPage()}
                                    disabled={!paginationRef.current?.canNextPage}
                                >
                                    Next →
                                </Button>
                            </nav>
                        </div>
                    </Pagination>
                </div>
            </main>

            <section className="flex items-center justify-center h-80 bg-gray-100 text-black">
                <div className="text-center">
                    <h1 className="text-4xl font-bold">Ready to get started?</h1>
                    <p className="mt-4 text-lg text-gray-600">Join us today and explore the possibilities!</p>
                    <Button variant="secondary" className="mt-6">
                        Get Started Now
                    </Button>
                </div>
            </section>

            <footer className="py-8 px-4 bg-white dark:bg-gray-900">
                <div className="container mx-auto text-center">
                    <p className="text-gray-700 dark:text-gray-300">&copy; 2025 YourCompany. All rights reserved.</p>
                    <div className="flex justify-center space-x-6 mt-4">
                        <a href="#" className="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                            Privacy Policy
                        </a>
                        <a href="#" className="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                            Terms of Service
                        </a>
                    </div>
                </div>
            </footer>
        </>
    )
}

export default Welcome
