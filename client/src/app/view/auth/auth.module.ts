import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterAuthModule } from "./router-auth.module";
import { FormsModule, ReactiveFormsModule } from "@angular/forms";
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';
import { SharedModule } from '../../modules/shared/shared.module';
import { ForgotComponent } from './forgot/forgot.component';
import { LoginComponent } from './login/login.component';
import { CandidateRegisterComponent } from './candidate-register/candidate-register.component';
import { BusinessRegisterComponent } from './business-register/business-register.component';
import { AuthComponent } from './auth.component';
import { NgxMyDatePickerModule } from 'ngx-mydatepicker';

@NgModule({
  imports: [
    CommonModule,
    RouterAuthModule,
    FormsModule,
    ReactiveFormsModule,
    NgbModule,
    SharedModule,
    NgxMyDatePickerModule.forRoot()
  ],
  declarations: [
    ForgotComponent,
    LoginComponent,
    CandidateRegisterComponent,
    BusinessRegisterComponent,
    AuthComponent
  ]
})
export class AuthModule { }
