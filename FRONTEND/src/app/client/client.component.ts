import { Component, OnInit } from '@angular/core';
import { Observable } from 'rxjs';
import { Client } from './client';
import { ClientService } from './client.service';

@Component({
  selector: 'app-client',
  templateUrl: './client.component.html',
  styleUrls: ['./client.component.css']
})
export class ClientComponent implements OnInit {

  constructor(private clientService: ClientService) { }

  client$: Observable<Client>;
  login : string = "";
  password : string = "";

  ngOnInit(): void { }

  onSubmit(){
    if(!this.client$) {
      this.client$ = this.clientService.login(this.login, this.password);
    } else {
      console.log(this.client$);
      this.clientService.saveClient(this.client$);
    }
  }
}
