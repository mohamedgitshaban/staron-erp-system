<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="{{asset("style/main.css")}}"/>
    <title>Qutation Review</title>
</head>
<body>
   <div class="container">
    <h1 >Quotation Review</h1>
    <div class="row">
        <div class="col-6">
            {{-- <h2>Client Information</h2> --}}
            <p>For Client: {{ $salesCrm->client->name }}</p>
            <p>Company: {{ $salesCrm->client->company }}</p>

        </div>
        <div class="col-6">
            {{-- <h2>Assigned By</h2> --}}
            <p>Assign by: {{ $salesCrm->assignedBy->name }}</p>
            <p>Supervisor: {{ $salesCrm->assignedBy->supervisor->name ?? 'N/A' }}</p>

        </div>


    </div>
    <h2>Technical Requests</h2>
    @foreach($salesCrm->technecalRequests as $request)
        {{-- <p>Status: {{ $request->qcstatus }}</p> --}}
        {{-- <p>Start Date: {{ $request->qcstartdate }}</p> --}}
        {{-- <p>End Date: {{ $request->qcenddate }}</p> --}}
        <div class="row">
            <p class="col-4 project-cost" >Total Cost: {{ $request->totalprice }} EGP</p>
            <p class="col-4 project-cost" >Total Gross Margin: {{ $latestQuotation->ProjectGrossMargin }} %</p>
            <p class="col-4 project-cost" >Total selling price: {{ $latestQuotation->TotalProjectSellingPrice }} EGP</p>
        </div>

        {{-- <h3>QC Applications</h3> --}}
        <table class="table">
            <thead>
              <tr >
                <th scope="col" >#</th>
                <th scope="col" class="title" >Application Name</th>
                <th scope="col" class="title">Total Cost</th>
                <th scope="col"class="title">grossmargen</th>
                <th scope="col"class="title">Saling Price</th>
                <th scope="col"></th>

                <th scope="col" class="title">Stock ID</th>
                <th scope="col"class="title">Quantity</th>
                <th scope="col"class="title">Price</th>
                <th scope="col"class="title">Description</th>
              </tr>
            </thead>
            <tbody>


        @foreach($request->qcApplecations as $application)
            <tr >
            <th rowspan="{{ count($application->qcApplecationItem) }}" scope="row">{{ $loop->iteration }}</th>
            <td rowspan="{{ count($application->qcApplecationItem) }}">{{ $application->name }}</td>
            <td rowspan="{{ count($application->qcApplecationItem) }}">{{ $application->totalcost }} EGP</td>
            <td rowspan="{{ count($application->qcApplecationItem) }}">{{ $application->grossmargen }} %</td>
            <td rowspan="{{ count($application->qcApplecationItem) }}">{{ $application->salingprice }} EGP</td>

            @foreach($application->qcApplecationItem as $item)
            <th  scope="row"> {{ $loop->parent->iteration }}.{{ $loop->iteration }}</th>
            <td >{{ $item->stockid }}</td>
            <td >{{ $item->quantity }}</td>
            <td >{{ $item->price }} EGP</td>
            <td >{{ $item->description }}</td>
            </tr>
            @endforeach

        @endforeach
    @endforeach
</tbody>
</table>
    <footer>
        <div class="row">
            <div class="col-3">
                <video id="myVideo" width="100%" height="100%" playsInline autoPlay loop muted>
                    <source src="{{ asset('Assets/preloader.mp4') }}" type="video/mp4">
                </video>
            </div>
            <div class="col-3 flex">
                <p>Location: 95 Abu Bakr Al Seddek ST., Safir Square, Heliopolis.</p>
            </div>
            <div class="col-3">

            </div>
        </div>
    </footer>
   </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script>
        // Wait until the video metadata is loaded to set playback rate
        document.getElementById('myVideo').addEventListener('loadedmetadata', function() {
            this.playbackRate = 0.5; // Set playback speed to half
        });
    </script>
</body>
</html>
