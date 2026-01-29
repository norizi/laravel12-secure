@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Senarai User</div>

                <div class="card-body">
                    
                    
                    <table class="table table-bordered">
                        <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($users as $user) 
                        <tr>
                            
                            <td>{{$user->name}}</td>
                            <td>{{$user->email}}</td>
                            <td>
                                <button type="button" class="btn btn-primary">Edit</button>
                                <a href="{{ route('users.destroya', $user->id) }}" class="btn btn-primary">Delete A</a>
                                <form action="{{ route('users.destroyb', $user->id) }}" method="POST" 
                                    onsubmit="return confirm('Anda pasti mahu padam data ini?')">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="btn btn-danger">
                                        Delete B
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>



                </div>
            </div>
        </div>
    </div>
</div>
@endsection
