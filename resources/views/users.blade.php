@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">


                 <a href="" class="btn btn-primary" 
                 data-bs-toggle="modal" data-bs-target="#myModal">
                                    Create User</a>

                                    <!-- The Modal -->
<div class="modal" id="myModal">
  <div class="modal-dialog">
    <div class="modal-content">

      <!-- Modal Header -->
      <div class="modal-header">
        <h4 class="modal-title">Modal Heading</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <!-- Modal body -->
      <div class="modal-body">
        
        
        <form action="{{route('users.store')}}" method="post">
        @csrf
        <div class="mb-3 mt-3">
            <label for="email" class="form-label">Name :</label>
            <input type="text" class="form-control" 
            name="name">
        </div>


        <div class="mb-3 mt-3">
            <label for="email" class="form-label">Email:</label>
            <input type="email" class="form-control" 
            name="email">
        </div>
        <div class="mb-3">
            <label for="pwd" class="form-label">Password:</label>
            <input type="password" class="form-control" 
            name="password">
        </div>
       
        

        <button type="submit" class="btn btn-primary">Submit</button>
        </form>



      </div>

      <!-- Modal footer -->
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
      </div>

    </div>
  </div>
</div>


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
                        @php
                            $encryptid=encrypt($user->id);
                        @endphp
                        <tr>
                            
                            <td>{{$user->name}}</td>
                            <td>{{$user->email}}</td>
                            <td>
                                <button type="button" class="btn btn-primary">Edit</button>
                                <a href="{{ route('users.destroya',$encryptid) }}" class="btn btn-primary">
                                    Delete A</a>
                                <form action="{{ route('users.destroyb', $encryptid) }}" method="POST" 
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
