<div id="page_title">
<h1>{title}</h1>
</div>


<div id="content">
    <div class="container">
        <div class="row">
            <div class="col-lg-3 col-md-6">
                <div class="panel panel-info">
                    <div class="panel-heading">
                        <div class="row">
                            <div class="col-xs-3">
                                <i class="fa fa-send fa-5x"></i>
                            </div>
                            <div class="col-xs-9 text-right">
                                <div class="huge">{fleet_count}</div>
                                <div>Active Airlines</div>
                            </div>
                        </div>
                    </div>
                    <a href="/fleet">
                        <div class="panel-footer">
                            <span class="pull-left">{fleet_count} airlines licensed</span>
                            <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                            <div class="clearfix"></div>
                        </div>
                    </a>

                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="panel panel-warning">
                    <div class="panel-heading">
                        <div class="row">
                            <div class="col-xs-3">
                                <i class="fa fa-suitcase fa-5x"></i>
                            </div>
                            <div class="col-xs-9 text-right">
                                <div class="huge">{flight_count}</div>
                                <div>Flights scheduled</div>
                            </div>
                        </div>
                    </div>
                    <a href="/flights">
                        <div class="panel-footer">
                            <span class="pull-left">View Details</span>
                            <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                            <div class="clearfix"></div>
                        </div>
                    </a>
                </div>
            </div>


            <div class="col-lg-3 col-md-6">
                <div class="panel panel-success">
                    <div class="panel-heading">
                        <div class="row">
                            <div class="col-xs-3">
                                <i class="fa fa-plane fa-5x"></i>
                            </div>
                            <div class="col-xs-9 text-right">
                                <div class="huge">{airport_count}</div>
                                <div>Airports</div>
                            </div>
                        </div>
                    </div>
                    <div class="panel-footer">
                        <span class="pull-left">{airport_list}</span>
                        <div class="clearfix"></div>
                    </div>
       
                </div>
            </div>

<!-- flight booking -->
            <form id="flight-booking-form">
                <div class="form-group col-md-5">
                    <label>Choose Departure Airport</label>
                    <select class="form-control" id="departureAirport" name = "departureAirport" >
                        {airports}
                            <option value={id}>{id}</option>
                        {/airports}
                    </select>
                </div>
                <div class="form-group col-md-5">
                    <label>Choose Destination Airport</label>
                    <select class="form-control" id="destinationAirport" name = "destinationAirport" >
                        {airports}
                            <option value={id}>{id}</option>
                        {/airports}
                    </select>
                </div>
                <div class="form-group col-md-5">
                    <button type="button" class="btn btn-default" onclick="search()">Search</button>
                </div>
            </form>
            <br><br>
            <table class="table" id="flight-booking-table" style="display:none;">
                <thead>
                <tr>
                    <th>Option</th>
                    <th>Departure Airport</th>
                    <th>Departure Time</th>                
                    <th>Destination Airport</th>
                    <th>Arrival Time</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>

    <script>
        function search() {
            var formData = $('#flight-booking-form').serialize()
            $.post({
                url:'/home/searchFlights',
                type:'POST',
                data:formData,
                success: (booking) => {
                    var table = document.getElementById('flight-booking-table');
                    $('tbody').remove();
                    var count = 0;
                    for(var match in booking) {
                        count++;
                        var rowspan = booking[match].length;
                        console.log(rowspan);
                        var body = document.createElement('TBODY');
                        for(var item in booking[match]) {
                            var flightinfo = booking[match][item];
                            var flightOptionNumber = document.createElement('TD');
                            if(item == 0) {
                                flightOptionNumber.innerHTML = count;
                                flightOptionNumber.rowSpan = rowspan;
                            }
                            var Depart = document.createElement('TD');
                            Depart.innerHTML = flightinfo['departure_airport_id'];
                            var Dest = document.createElement('TD');
                            Dest.innerHTML = flightinfo['arrival_airport_id'];
                            var DepartTime = document.createElement('TD');
                            DepartTime.innerHTML = flightinfo['departure_time'];
                            var DestTime = document.createElement('TD');
                            DestTime.innerHTML = flightinfo['arrival_time'];
                            var row = document.createElement('TR');
                            if(item == 0) {row.appendChild(flightOptionNumber) };
                            row.appendChild(Depart);
                            row.appendChild(DepartTime);
                            row.appendChild(Dest);
                            row.appendChild(DestTime);
                            body.appendChild(row);
                            table.append(body);
                            table.style.display = 'block';
                        }
                    }
                },
                error: (error) => {
                    console.log(error);
                },
            });
        }
    </script>

</div>
