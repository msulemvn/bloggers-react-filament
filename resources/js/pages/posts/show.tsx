import React, { useState, useRef, useEffect } from 'react';
import axios from 'axios';
import { toast } from 'sonner';
import { usePage } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Textarea } from '@/components/ui/textarea';
import { Badge } from '@/components/ui/badge';
import { SimpleAppHeader } from '@/components/simple-app-header';

interface Comment {
  id: number;
  message: string;
  from: { id: number; name: string } | null;
  created_at: string;
  comments: Comment[];
}

interface Post {
  id: number;
  title: string;
  description: string;
  content: string;
  feature_image?: string;
  tags: { id: number; name: string }[];
  comments: Comment[];
  author: { id: number; name: string };
  user_id: number;
  is_published: boolean;
  slug: string;
  status: string;
  comments_count: number;
}

interface PostResponse {
  data: Post;
}

const PostPage = () => {
  const { props } = usePage<{ post: PostResponse; auth: { user: { id: number; name: string } | null } }>();
  const initialPost = props.post.data;
  const user = props.auth.user;

  const [postData, setPostData] = useState<Post>(initialPost);
  const [comments, setComments] = useState<Comment[]>(initialPost.comments);
  const [newComment, setNewComment] = useState('');
  const [newReply, setNewReply] = useState('');
  const [replyingTo, setReplyingTo] = useState<number | null>(null);
  const [editing, setEditing] = useState<{ id: number; content: string } | null>(null);

  const replyInputRefs = useRef<{ [key: number]: HTMLTextAreaElement | null }>({});

  useEffect(() => {
    if (replyingTo !== null && replyInputRefs.current[replyingTo]) {
      const el = replyInputRefs.current[replyingTo];
      const offset = 150;
      const top = el.getBoundingClientRect().top + window.scrollY - offset;
      window.scrollTo({ top, behavior: 'smooth' });

      setTimeout(() => {
        el?.focus();
      }, 300);
    }
  }, [replyingTo]);

  const handlePostComment = async () => {
    if (!newComment.trim()) return;
    try {
      const { data } = await axios.post('/comments', {
        commentable_id: postData.id,
        commentable_type: 'post',
        body: newComment,
      });
      setComments((prev) => [...prev, data.data]);
      setPostData((prev) => ({
        ...prev,
        comments_count: prev.comments_count + 1,
      }));
      setNewComment('');
      toast.success('Comment posted');
    } catch {
      toast.error('Failed to post comment');
    }
  };

  const handlePostReply = async (parentId: number) => {
    if (!newReply.trim()) return;
    try {
      const { data } = await axios.post('/comments', {
        commentable_id: postData.id,
        commentable_type: 'post',
        parent_comment_id: parentId,
        body: newReply,
      });
      const reply = data.data;
      const insertReply = (items: Comment[]): Comment[] =>
        items.map((comment) =>
          comment.id === parentId
            ? { ...comment, comments: [...(comment.comments ?? []), reply] }
            : { ...comment, comments: insertReply(comment.comments ?? []) }
        );
      setComments((prev) => insertReply(prev));
      setPostData((prev) => ({
        ...prev,
        comments_count: prev.comments_count + 1,
      }));
      setNewReply('');
      setReplyingTo(null);
      toast.success('Reply added');
    } catch {
      toast.error('Failed to reply');
    }
  };

  const handleDelete = async (id: number) => {
    try {
      await axios.delete(`/comments/${id}`);
      const removeComment = (items: Comment[]): Comment[] =>
        items
          .filter((comment) => comment.id !== id)
          .map((comment) => ({
            ...comment,
            comments: removeComment(comment.comments ?? []),
          }));
      setComments(removeComment(comments));
      setPostData((prev) => ({
        ...prev,
        comments_count: Math.max(0, prev.comments_count - 1),
      }));
      toast.success('Comment deleted');
    } catch {
      toast.error('Delete failed');
    }
  };

  const handleUpdate = async () => {
    if (!editing?.content.trim()) return;
    try {
      const { data } = await axios.put(`/comments/${editing.id}`, { body: editing.content });
      const updateComment = (items: Comment[]): Comment[] =>
        items.map((comment) =>
          comment.id === editing.id
            ? { ...comment, ...data.data }
            : { ...comment, comments: updateComment(comment.comments ?? []) }
        );
      setComments(updateComment(comments));
      setEditing(null);
      toast.success('Updated');
    } catch {
      toast.error('Update failed');
    }
  };

  const renderCommentThread = (comment: Comment): React.JSX.Element => (
    <div key={comment.id} className="mb-4">
      <div className="border p-4 rounded-md">
        <div className="text-sm text-muted-foreground mb-1">
          {comment.from?.name || 'Anonymous'} • {comment.created_at}
        </div>
        {editing?.id === comment.id ? (
          <>
            <Textarea
              value={editing.content}
              onChange={(e) => setEditing({ ...editing, content: e.target.value })}
              className="mt-2"
            />
            <div className="mt-3 flex gap-2">
              <Button className="px-4 py-2 text-sm font-medium rounded-md" onClick={handleUpdate}>
                Save
              </Button>
              <Button
                variant="outline"
                className="px-4 py-2 text-sm font-medium rounded-md"
                onClick={() => setEditing(null)}
              >
                Cancel
              </Button>
            </div>
          </>
        ) : (
          <>
            <p className="text-sm mt-2">{comment.message}</p>
            {user && (
              <div className="mt-3 flex gap-2 text-sm">
                <button onClick={() => setReplyingTo(comment.id)} className="hover:underline text-sm">
                  Reply
                </button>
                {user.id === comment.from?.id && (
                  <>
                    <button
                      onClick={() => setEditing({ id: comment.id, content: comment.message })}
                      className="hover:underline text-sm"
                    >
                      Edit
                    </button>
                    <button
                      onClick={() => handleDelete(comment.id)}
                      className="hover:underline text-sm text-destructive"
                    >
                      Delete
                    </button>
                  </>
                )}
              </div>
            )}
          </>
        )}
      </div>

      {replyingTo === comment.id && (
        <div className="mt-3">
          <Textarea
            ref={(el) => {
              replyInputRefs.current[comment.id] = el;
            }}
            value={newReply}
            onChange={(e) => setNewReply(e.target.value)}
            className="mt-2"
            placeholder="Write a reply..."
          />
          <div className="mt-3 flex gap-2">
            <Button
              className="px-4 py-2 text-sm font-medium rounded-md"
              onClick={() => handlePostReply(comment.id)}
            >
              Reply
            </Button>
            <Button
              variant="outline"
              className="px-4 py-2 text-sm font-medium rounded-md"
              onClick={() => setReplyingTo(null)}
            >
              Cancel
            </Button>
          </div>
        </div>
      )}

      {(comment.comments || []).length > 0 && (
        <div className="mt-4 pl-4 border-l">
          {comment.comments.map((reply) => renderCommentThread(reply))}
        </div>
      )}
    </div>
  );

  return (<>
    <SimpleAppHeader showNav={true} authenticated={user} />
    <div className="container mx-auto max-w-screen-md mt-10 text-foreground">
      <div className="mt-6">
        <h1 className="text-3xl font-semibold">{postData.title}</h1>
        <p className="text-muted-foreground mt-1">{postData.description}</p>
        {postData.feature_image && (
          <img src={postData.feature_image} alt="Post feature" className="rounded-md w-full mt-4" />
        )}
        <div className="prose max-w-none mt-6" dangerouslySetInnerHTML={{ __html: postData.content }} />
        <div className="mt-4 flex flex-wrap gap-2">
          {postData.tags.map((tag) => (
            <Badge key={tag.id} variant="secondary">
              {tag.name}
            </Badge>
          ))}
        </div>
      </div>

      <div className="mt-10">
        <h2 className="text-xl font-medium mb-4">Comments ({postData.comments_count})</h2>
        {user ? (
          <>
            <Textarea
              value={newComment}
              onChange={(e) => setNewComment(e.target.value)}
              className="w-full"
              placeholder="Leave a comment..."
              rows={3}
            />
            <Button onClick={handlePostComment} className="mt-3 px-4 py-2 text-sm font-medium rounded-md">
              Post
            </Button>
          </>
        ) : (
          <p className="text-muted-foreground">Please log in to comment.</p>
        )}
        <div className="mt-6">
          {comments.map((comment) => renderCommentThread(comment))}
        </div>
      </div>
    </div>
  </>);
};

export default PostPage;
