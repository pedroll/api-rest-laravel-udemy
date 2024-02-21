<h1>Posts</h1>
<ul>
    @foreach($posts as $post)
        <h2>{{$post->title}}</h2>
        <p><span style="color:gray">{{$post->user->name}} - {{$post->category->name}}</span></p>
        <p>{{$post->content}}</p>
        <hr>
    @endforeach
</ul>
<hr>
<h1>Categorias</h1>
<ul>
    @foreach($categories as $category)
        <li><h2>{{$category->name}}</h2>
            <hr>
            <ul>
                @foreach($category->posts as $post)
                    <li>
                        <h3>{{$post->title}}</h3>
                        <p><span style="color:gray">{{$post->user->name}} - {{$post->category->name}}</span></p>
                        <p>{{$post->content}}</p>
                        <hr>
                    </li>
                @endforeach
            </ul>
            @endforeach
        </li>
</ul>
<hr>
<h1>user</h1>
<ul>
    @foreach($users as $user)
        <li>
            {{ $user }}
        </li>
    @endforeach
</ul>
