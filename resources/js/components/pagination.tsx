import React, {
  useMemo,
  forwardRef,
  useImperativeHandle,
  PropsWithChildren,
} from 'react'
import { router } from '@inertiajs/react'
import type { PaginationMeta } from '@/types'

export interface PaginationHandle {
  handleClickPrevPage: () => void
  handleClickNextPage: () => void
  canPreviousPage: boolean
  canNextPage: boolean
}

interface PaginationProps {
  meta: PaginationMeta
  onPrevPage?: () => void
  onNextPage?: () => void
  children?: React.ReactNode | ((helpers: PaginationHandle) => React.ReactNode)
}

const Pagination = forwardRef<PaginationHandle, PropsWithChildren<PaginationProps>>(
  ({ meta, onPrevPage, onNextPage, children }, ref) => {
    const currentPage = meta?.current_page ?? 1
    const pageCount = meta?.last_page ?? 1
    const links = meta?.links ?? []

    const canPreviousPage = currentPage > 1
    const canNextPage = currentPage < pageCount

    const handleClickPrevPage = () => {
      if (canPreviousPage) {
        const prev = links.find((link) => link.label === '&laquo; Previous')
        if (prev?.url) {
          router.get(prev.url, {}, { preserveScroll: true })
          onPrevPage?.()
        }
      }
    }

    const handleClickNextPage = () => {
      if (canNextPage) {
        const next = links.find((link) => link.label === 'Next &raquo;')
        if (next?.url) {
          router.get(next.url, {}, { preserveScroll: true })
          onNextPage?.()
        }
      }
    }

    useImperativeHandle(ref, () => ({
      handleClickPrevPage,
      handleClickNextPage,
      canPreviousPage,
      canNextPage,
    }))

    const paginationHelpers: PaginationHandle = useMemo(
      () => ({
        handleClickPrevPage,
        handleClickNextPage,
        canPreviousPage,
        canNextPage,
      }),
      [canPreviousPage, canNextPage, links]
    )

    return (
      <>
        {typeof children === 'function'
          ? children(paginationHelpers)
          : children}
      </>
    )
  }
)

Pagination.displayName = 'Pagination'
export default Pagination
